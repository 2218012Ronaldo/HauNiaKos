<?php

namespace App\Http\Controllers;

use App\Interface\BoardingHouseRepositoryInterface;
use App\Interface\CategoryRepositoryInterface;
use App\Interface\CityRepositoryInterface;
use Illuminate\Http\Request;

class BoardingHouseController extends Controller
{
    private CityRepositoryInterface $cityRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private BoardingHouseRepositoryInterface $boardingHouseRepository;

    public function __construct(
        CityRepositoryInterface $cityRepository,
        CategoryRepositoryInterface $categoryRepository,
        BoardingHouseRepositoryInterface $boardingHouseRepository,
    ) {
        $this->cityRepository = $cityRepository;
        $this->categoryRepository = $categoryRepository;
        $this->boardingHouseRepository = $boardingHouseRepository;
    }

    public function show(Request $request, $slug)
    {
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);

        // Kirim data ke view
        return view('pages.boarding-house.show', compact('boardingHouse'));
    }

    // Method untuk halaman "See all"
    public function showAll(Request $request)
    {
        $isPriceFilterEnabled = $request->boolean('price_enabled');
        $isDistanceFilterEnabled = $request->boolean('distance_enabled');
        $ratingCategory = $request->query('rating_category', 'all');

        // Convert rating category to min/max values
        $ratingMinMax = $this->convertRatingCategoryToMinMax($ratingCategory);

        $filters = [
            'price_max' => $isPriceFilterEnabled ? $request->query('price_max') : null,
            'distance_max' => $isDistanceFilterEnabled ? $request->query('distance_max') : null,
            'rating_min' => $ratingMinMax['rating_min'],
            'rating_max' => $ratingMinMax['rating_max'],
            'facilities' => $request->query('facilities', []),
        ];

        // Ambil semua recommended boarding houses (tanpa limit) dengan filter dari home
        $boardingHouses = $this->boardingHouseRepository->getRecommendedBoardingHouse(
            100,
            $filters,
        );

        return view('pages.boarding-house.show-all', compact('boardingHouses'));
    }

    private function convertRatingCategoryToMinMax(string $category): array
    {
        return match ($category) {
            '1' => ['rating_min' => 1, 'rating_max' => 2],
            '2' => ['rating_min' => 2, 'rating_max' => 3],
            '3' => ['rating_min' => 3, 'rating_max' => 4],
            '4' => ['rating_min' => 4, 'rating_max' => 5],
            '5' => ['rating_min' => 5, 'rating_max' => null],
            default => ['rating_min' => null, 'rating_max' => null],
        };
    }

    public function rooms($slug)
    {
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);

        return view('pages.boarding-house.rooms', compact('boardingHouse'));
    }

        public function find()
    {
        $categories = $this->categoryRepository->getAllCategories();
        $cities = $this->cityRepository->getAllCities();

        return view('pages.boarding-house.find', compact('categories', 'cities'));
    }

    public function findResults(Request $request)
    {
        $boardingHouses = $this->boardingHouseRepository->getAllBoardingHouses(
            $request->search,
            $request->city,
            $request->category,
        );

        return view('pages.boarding-house.search-result', compact('boardingHouses'));
    }
}