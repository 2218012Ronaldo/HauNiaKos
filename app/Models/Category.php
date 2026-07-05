<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['image', 'name', 'slug'];

    public function boardingHouses(){
        return $this->hasMany(BoardingHouse::class);
    }

    public function baording_houses(){
        return $this->boardingHouses();
    }

    public function boardingHousesWithAvailableRooms()
    {
        return $this->hasMany(BoardingHouse::class)->whereHas('rooms', function ($query) {
            $query->where('is_available', true);
        });
    }
}
