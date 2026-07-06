<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
     use HasFactory, SoftDeletes;

     protected static function booted(): void
    {
        static::saved(function (Transaction $transaction): void {
            $transaction->syncRoomAvailability();
        });

        static::deleted(function (Transaction $transaction): void {
            $transaction->syncRoomAvailability();
        });

        static::restored(function (Transaction $transaction): void {
            $transaction->syncRoomAvailability();
        });
    }

    protected $fillable = [
        'code',
        'boarding_house_id',
        'user_id',
        'room_id',
        'name',
        'email',
        'phone_number',
        'gender',
        'payment_method',
        'payment_status',
        'approval_status',
        'approved_at',
        'rejected_at',
        'start_date',
        'duration',
        'total_amount',
        'transaction_date',
    ];

    protected $casts = [
        'duration' => 'integer',
        'start_date' => 'date',
    ];

    // protected static function booted()
    // {
    //     static::saved(function (Transaction $transaction) {
    //         if ($transaction->wasChanged('payment_status')) {
    //             $transaction->syncRoomAvailability();
    //         }
    //     });
    // }

    public function boardingHouse(){
        return $this->belongsTo(BoardingHouse::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room (){
        return $this->belongsTo(Room::class);
    }

    private function syncRoomAvailability(): void
    {
        $room = $this->room;

        if ($room === null) {
            return;
        }

        if ($this->isPaid()) {
            if ($room->is_available) {
                $room->update(['is_available' => false]);
            }

            return;
        }

        if ($room->transactions()->where('payment_status', 'paid')->exists()) {
            return;
        }

        if (!$room->is_available) {
            $room->update(['is_available' => true]);
        }
    }

    private function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPendingOwner(): bool
    {
        return $this->approval_status === 'pending_owner';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function approve(): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'approval_status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }
}
