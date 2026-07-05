<?php

namespace App\Repositories;

use App\Interface\CityRepositoryInterface;
use App\Models\City;
use GuzzleHttp\ClientInterface;

class CityRepository implements CityRepositoryInterface{
    public function getAllCities(){
        return City::withCount([
            'boardingHousesWithAvailableRooms as available_boarding_houses_count'
        ])->get();
    }


    public function getCityBySlug($slug){
        return City::withCount([
            'boardingHousesWithAvailableRooms as available_boarding_houses_count'
        ])->where('slug', $slug)->first();
    }

}