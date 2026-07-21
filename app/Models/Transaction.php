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
            // Skip room availability sync for extension transactions
            if (! $transaction->is_extension) {
                $transaction->syncRoomAvailability();
            }
        });

        static::deleted(function (Transaction $transaction): void {
            // Skip room availability sync for extension transactions
            if (! $transaction->is_extension) {
                $transaction->syncRoomAvailability();
            }
        });

        static::restored(function (Transaction $transaction): void {
            // Skip room availability sync for extension transactions
            if (! $transaction->is_extension) {
                $transaction->syncRoomAvailability();
            }
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
        'parent_transaction_id',
        'is_extension',
    ];

    protected $casts = [
        'duration' => 'integer',
        'start_date' => 'date',
        'is_extension' => 'boolean',
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

    public function getEndDateAttribute()
    {
        return \Carbon\Carbon::parse($this->start_date)->addMonths($this->duration);
    }

    public function canBeExtended(): bool
    {
        return $this->isPaid() && 
               $this->payment_method === 'full_payment' && 
               $this->getEndDateAttribute()->isFuture();
    }

    public function getTotalDurationAttribute(): int
    {
        // If this is a completion payment (parent_transaction_id == self), return original duration
        if ($this->parent_transaction_id === $this->id) {
            return $this->duration;
        }
        
        // If this is an extension, calculate total duration including parent
        if ($this->is_extension && $this->parent_transaction_id) {
            $parentTransaction = Transaction::find($this->parent_transaction_id);
            if ($parentTransaction) {
                return $parentTransaction->duration + $this->duration;
            }
        }
        
        // If this has extensions, calculate total duration including all extensions (excluding completion payments)
        $extensions = Transaction::where('parent_transaction_id', $this->id)
            ->where('is_extension', true)
            ->whereColumn('parent_transaction_id', '!=', 'id') // Exclude completion payments
            ->get();
        if ($extensions->isNotEmpty()) {
            $total = $this->duration;
            foreach ($extensions as $extension) {
                $total += $extension->duration;
            }
            return $total;
        }
        
        // Return current duration if no extensions
        return $this->duration;
    }

    public function getFinalEndDateAttribute()
    {
        // If this is an extension, return its own end date
        if ($this->is_extension) {
            return $this->getEndDateAttribute();
        }
        
        // If this is an original booking, return its own end date (not including extensions)
        // This ensures the owner sees the original booking period
        return $this->getEndDateAttribute();
    }

    public function canCompletePayment(): bool
    {
        $canComplete = $this->payment_method === 'down_payment' && 
                       $this->isApproved();
        
        \Log::info('canCompletePayment check', [
            'transaction_code' => $this->code,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'is_approved' => $this->isApproved(),
            'can_complete' => $canComplete
        ]);
        
        return $canComplete;
    }

    public function getRemainingPaymentAmount(): float
    {
        if ($this->payment_method !== 'down_payment') {
            return 0;
        }
        
        // Down payment is 30%, remaining is 70%
        return $this->total_amount * 0.7;
    }
}