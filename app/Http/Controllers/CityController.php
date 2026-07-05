<?php

namespace App\Http\Controllers;

use App\Interface\BoardingHouseRepositoryInterface;
use App\Interface\CityRepositoryInterface;
use Illuminate\Http\Request;

class CityController extends Controller
{
     private BoardingHouseRepositoryInterface $boardingHouseRepository;
    private CityRepositoryInterface $cityRepository;

    // Dependency Injection: Laravel otomatis inject repository
    public function __construct(
        BoardingHouseRepositoryInterface $boardingHouseRepository,
        CityRepositoryInterface $cityRepository,
    ) {
        $this->boardingHouseRepository = $boardingHouseRepository;
        $this->cityRepository = $cityRepository;
    }

    public function show(Request $request, $slug)
    {
        $city = $this->cityRepository->getCityBySlug($slug);
        $boardingHouses = $this->boardingHouseRepository->getBoardingHouseByCitySlug($slug);

        return view('pages.city.show', compact('city', 'boardingHouses'));
    }

    public function showAll(Request $request)
    {
        $cities = $this->cityRepository->getAllCities();

        return view('pages.city.show-all', compact('cities'));
    }
}
