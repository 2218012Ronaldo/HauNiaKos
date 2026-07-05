<?php

namespace App\Interface;

interface CityRepositoryInterface
{
    public function getAllCities();

    public function getCityBySlug($slug);
}
