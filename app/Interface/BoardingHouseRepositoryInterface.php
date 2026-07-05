<?php

namespace App\Interface;

interface BoardingHouseRepositoryInterface
{
    public function getAllBoardingHouses($search = null, $city = null, $category = null);

    public function getRecommendedBoardingHouse($limit = 5, array $filters = []);

    public function getBestByRating($limit = 5);

    public function getBoardingHouseByCitySlug($slug);

    public function getBoardingHouseByCategorySlug($slug);

    public function getBoardingHouseBySlug($slug);

    public function getBoardingHouseRoomById($id);
}
