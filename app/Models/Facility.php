<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'icon', 'weight'];

    public function boardingHouses(){
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_facilities');
    }
}
