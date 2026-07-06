<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    
    use HasFactory, SoftDeletes;

    protected $casts = [
        'price_per_month' => 'float',
        'square_feet' => 'integer',
        'capacity' => 'integer',
        'is_available' => 'boolean',
    ];

    protected $appends = [
        'price_per_month_usd',
    ];

    

    protected $fillable = [
        'boarding_house_id',
        'name',
        'room_type',
        'square_feet',
        'capacity',
        'price_per_month',
        'is_available',
    ];

    public function boardingHouse(){
        return $this->belongsTo(BoardingHouse::class);
    }

    public function roomImages(){
        return $this->hasMany(RoomImage::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function availabilityLabel(): string
    {
        return $this->is_available ? 'Available' : 'Unavailable';
    }

    public function getPricePerMonthUsdAttribute(): float
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->convertToUsdNormalized($this->price_per_month);
    }
}
