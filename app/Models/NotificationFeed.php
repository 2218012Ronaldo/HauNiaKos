<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class NotificationFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_user_id',
        'recipient_role',
        'kind',
        'status',
        'reference',
        'title',
        'body',
        'payload',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query
            ->where('recipient_role', $user->role)
            ->where(function (Builder $recipientQuery) use ($user): void {
                if ($user->isAdmin()) {
                    $recipientQuery->whereNull('recipient_user_id');

                    return;
                }

                // For regular users, include notifications where recipient_user_id matches
                // OR recipient_user_id is null (guest bookings) and payload email matches user's email
                $recipientQuery
                    ->where('recipient_user_id', $user->id)
                    ->orWhere(function (Builder $guestQuery) use ($user): void {
                        $guestQuery
                            ->whereNull('recipient_user_id')
                            ->where('payload', 'like', '%'.$user->email.'%');
                    });
            });
    }

    public static function recordForRole(
        string $recipientRole,
        ?int $recipientUserId,
        string $kind,
        string $status,
        string $reference,
        string $title,
        string $body,
        array $payload = [],
    ): self {
        return static::updateOrCreate(
            [
                'recipient_role' => $recipientRole,
                'recipient_user_id' => $recipientUserId,
                'kind' => $kind,
                'reference' => $reference,
                'status' => $status,
            ],
            [
                'title' => $title,
                'body' => $body,
                'payload' => $payload,
                'read_at' => null,
            ],
        );
    }

    public static function recordBookingPending(Transaction $transaction): void
    {
        $transaction->loadMissing('boardingHouse', 'room');

        $ownerId = $transaction->boardingHouse?->owner_id;

        if (! $ownerId) {
            return;
        }

        static::recordForRole(
            'owner_kost',
            (int) $ownerId,
            'booking',
            'pending',
            (string) $transaction->code,
            'New booking from '.($transaction->name ?? 'a customer'),
            ($transaction->name ? $transaction->name : 'A customer').
                ' has made a booking and is waiting for your approval.',
            static::payloadFromTransaction($transaction),
        );
    }

    public static function recordBookingApproved(Transaction $transaction): void
    {
        $transaction->loadMissing('user', 'boardingHouse', 'room');

        // Update pending notification status to approved for owner
        static::query()
            ->where('kind', 'booking')
            ->where('status', 'pending')
            ->where('reference', $transaction->code)
            ->where('recipient_role', 'owner_kost')
            ->update([
                'status' => 'approved',
                'title' => 'Booking Approved',
                'body' => 'You have approved the booking from '.
                    ($transaction->name ?? 'a customer').
                    '.',
                'updated_at' => now(),
            ]);

        $payload = static::payloadFromTransaction($transaction);

        $user = $transaction->user;

        if (! $user && $transaction->email) {
            $user = User::query()->where('email', $transaction->email)->first();
        }

        // Create notification for user if found
        if ($user?->isUser()) {
            static::recordForRole(
                'user',
                (int) $user->getKey(),
                'booking',
                'approved',
                (string) $transaction->code,
                'Your booking has been approved',
                'Your booking for '.
                    ($transaction->boardingHouse?->name ?? 'the boarding house').
                    ' has been approved by the owner. You can now proceed with payment.',
                $payload,
            );
        }

        // Also create notification for guest users (users not logged in)
        // by using email as recipient identifier
        if (! $user && $transaction->email) {
            static::recordForRole(
                'user',
                null,
                'booking',
                'approved',
                (string) $transaction->code,
                'Your booking has been approved',
                'Your booking for '.
                    ($transaction->boardingHouse?->name ?? 'the boarding house').
                    ' has been approved by the owner. You can now proceed with payment.',
                $payload,
            );
        }
    }

    public static function recordBookingRejected(Transaction $transaction): void
    {
        $transaction->loadMissing('user', 'boardingHouse', 'room');

        // Update pending notification status to rejected for owner
        static::query()
            ->where('kind', 'booking')
            ->where('status', 'pending')
            ->where('reference', $transaction->code)
            ->where('recipient_role', 'owner_kost')
            ->update([
                'status' => 'not_approved',
                'title' => 'Booking Rejected',
                'body' => 'You have rejected the booking from '.
                    ($transaction->name ?? 'a customer').
                    '.',
                'updated_at' => now(),
            ]);

        $payload = static::payloadFromTransaction($transaction);

        $user = $transaction->user;

        if (! $user && $transaction->email) {
            $user = User::query()->where('email', $transaction->email)->first();
        }

        if ($user?->isUser()) {
            static::recordForRole(
                'user',
                (int) $user->getKey(),
                'booking',
                'not_approved',
                (string) $transaction->code,
                'Your booking has been rejected',
                'Your booking for '.
                    ($transaction->boardingHouse?->name ?? 'the boarding house').
                    ' has been rejected by the owner.',
                $payload,
            );
        }
    }

    public static function recordPaymentSuccess(Transaction $transaction): void
    {
        $transaction->loadMissing('user', 'boardingHouse', 'room');

        // Delete approved notification for this transaction for user
        static::query()
            ->where('kind', 'booking')
            ->where('status', 'approved')
            ->where('reference', $transaction->code)
            ->where('recipient_role', 'user')
            ->delete();

        $payload = static::payloadFromTransaction($transaction);

        if ($transaction->boardingHouse?->owner_id) {
            static::recordForRole(
                'owner_kost',
                (int) $transaction->boardingHouse->owner_id,
                'payment',
                'paid',
                (string) $transaction->code,
                'Payment received from '.($transaction->name ?? 'a customer'),
                ($transaction->name ? $transaction->name : 'A customer').
                    ' has completed the payment.',
                $payload,
            );
        }

        $user = $transaction->user;

        if (! $user && $transaction->email) {
            $user = User::query()->where('email', $transaction->email)->first();
        }

        if ($user?->isUser()) {
            static::recordForRole(
                'user',
                (int) $user->getKey(),
                'payment',
                'paid',
                (string) $transaction->code,
                'Your booking is paid: '.($transaction->name ?? 'a customer'),
                'Your payment has been confirmed successfully.',
                $payload,
            );
        }

        static::recordForRole(
            'admin',
            null,
            'payment',
            'paid',
            (string) $transaction->code,
            'Booking paid: '.($transaction->name ?? 'a customer'),
            ($transaction->name ? $transaction->name : 'A customer').
                ' has completed the payment for booking.',
            $payload,
        );
    }

    public function toClientArray(): array
    {
        $payload = $this->payload ?? [];

        return [
            'id' => 'feed-'.$this->getKey(),
            'kind' => $this->kind,
            'status' => $this->status,
            'title' => $this->title,
            'body' => $this->body,
            'trx' => $this->reference,
            'kos_name' => data_get($payload, 'kos_name', 'the boarding house'),
            'customer_name' => data_get($payload, 'customer_name'),
            'customer_email' => data_get($payload, 'customer_email'),
            'customer_phone' => data_get($payload, 'customer_phone'),
            'kos_slug' => data_get($payload, 'kos_slug'),
            'checkout_data' => data_get($payload, 'checkout_data', []),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public static function feedForUser(User $user, int $limit = 10): Collection
    {
        return static::query()
            ->forUser($user)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (self $notificationFeed): array => $notificationFeed->toClientArray())
            ->values();
    }

    private static function payloadFromTransaction(Transaction $transaction): array
    {
        return [
            'booking_id' => $transaction->getKey(),
            'trx' => $transaction->code,
            'kos_name' => $transaction->boardingHouse?->name ?? 'the boarding house',
            'customer_name' => $transaction->name,
            'customer_email' => $transaction->email,
            'customer_phone' => $transaction->phone_number,
            'kos_slug' => $transaction->boardingHouse?->slug,
            'room_type' => $transaction->room?->name,
            'total_price' => (string) $transaction->total_amount,
            'checkout_data' => [
                'booking_id' => $transaction->getKey(),
                'trx' => $transaction->code,
                'kos_name' => $transaction->boardingHouse?->name ?? 'the boarding house',
                'customer_name' => $transaction->name,
                'customer_email' => $transaction->email,
                'customer_phone' => $transaction->phone_number,
                'kos_slug' => $transaction->boardingHouse?->slug,
                'room_type' => $transaction->room?->name,
                'total_price' => (string) $transaction->total_amount,
            ],
        ];
    }
}