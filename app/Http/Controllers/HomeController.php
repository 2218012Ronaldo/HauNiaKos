<?php

namespace App\Http\Controllers;

use App\Interface\BoardingHouseRepositoryInterface;
use App\Interface\CategoryRepositoryInterface;
use App\Interface\CityRepositoryInterface;
use App\Models\Facility;
use App\Models\NotificationFeed;
use Illuminate\Http\Request;

class HomeController extends Controller
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

    public function index(Request $request)
    {
        $search = $request->search;
        $city = $request->city;
        $category = $request->category;
        $isPriceFilterEnabled = $request->boolean('price_enabled');
        $isDistanceFilterEnabled = $request->boolean('distance_enabled');
        $ratingCategory = $request->query('rating_category', 'all');

        // Convert rating category to min/max values
        $ratingMinMax = $this->convertRatingCategoryToMinMax($ratingCategory);

        $filters = [
            'price_max' => $isPriceFilterEnabled ? $request->query('price_max') : null,
            'distance_max' => $isDistanceFilterEnabled ? $request->query('distance_max') : null,
            'rating_min' => $ratingMinMax['rating_min'],
            'facilities' => $request->query('facilities', []),
        ];

        $boardingHouses = $this->boardingHouseRepository->getAllBoardingHouses(
            $search,
            $city,
            $category,
        );
        $categories = $this->categoryRepository->getAllCategories();
        $recommendedBoardingHouse = $this->boardingHouseRepository->getRecommendedBoardingHouse(
            5,
            $filters,
        );
        $cities = $this->cityRepository->getAllCities();
        $bestByRating = $this->boardingHouseRepository->getBestByRating(5);
        $facilities = Facility::query()
            ->select(['id', 'name', 'icon'])
            ->orderBy('name', 'asc')
            ->cursor();
        $notifications = [];

        if ($request->user()) {
            $notifications = NotificationFeed::feedForUser($request->user(), 10)->all();
        }

        // Selected filter states for form state persistence
        $selectedPriceMax = $request->query('price_max', 5000000);
        $selectedDistanceMax = $request->query('distance_max', 10);
        $selectedRatingCategory = $request->query('rating_category', 'all');
        $selectedFacilityIds = collect($request->query('facilities', []))->map(
            fn ($id) => (int) $id,
        );

        // Build see-all URL with active filters
        $seeAllQuery = [];
        if ($isPriceFilterEnabled) {
            $seeAllQuery['price_enabled'] = '1';
            $seeAllQuery['price_max'] = $selectedPriceMax;
        }
        if ($isDistanceFilterEnabled) {
            $seeAllQuery['distance_enabled'] = '1';
            $seeAllQuery['distance_max'] = $selectedDistanceMax;
        }
        if ($selectedRatingCategory !== 'all') {
            $seeAllQuery['rating_category'] = $selectedRatingCategory;
        }
        if ($selectedFacilityIds->isNotEmpty()) {
            $seeAllQuery['facilities'] = $selectedFacilityIds->all();
        }
        $seeAllUrl = route('boarding-house.show-all', $seeAllQuery);

        return view(
            'pages.home',
            compact(
                'categories',
                'recommendedBoardingHouse',
                'boardingHouses',
                'bestByRating',
                'cities',
                'facilities',
                'isPriceFilterEnabled',
                'selectedPriceMax',
                'isDistanceFilterEnabled',
                'selectedDistanceMax',
                'selectedRatingCategory',
                'selectedFacilityIds',
                'seeAllUrl',
                'notifications',
            ),
        );
    }

    private function convertRatingCategoryToMinMax(string $category): array
    {
        return match ($category) {
            '3' => ['rating_min' => 3, 'rating_max' => null],
            '4' => ['rating_min' => 4, 'rating_max' => null],
            '5' => ['rating_min' => 5, 'rating_max' => null],
            default => ['rating_min' => null, 'rating_max' => null],
        };
    }
}