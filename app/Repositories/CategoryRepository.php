<?php

namespace App\Repositories;

use App\Interface\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAllCategories()
    {
        return Category::withCount([
            'boardingHousesWithAvailableRooms as available_boarding_houses_count'
        ])->get();
    }

    public function getCategoryBySlug($slug)
    {
        return Category::withCount([
            'boardingHousesWithAvailableRooms as available_boarding_houses_count'
        ])
        ->where('slug', $slug)->first();
    }
    
}
