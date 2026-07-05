<?php

namespace App\Repositories;

use App\Interface\BoardingHouseRepositoryInterface;
use App\Models\BoardingHouse;
use App\Models\CriteriaWeight;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BoardingHouseRepository implements BoardingHouseRepositoryInterface
{
    private const DEFAULT_CITY_LAT = -8.558611;

    private const DEFAULT_CITY_LNG = 125.573056;

    public function getAllBoardingHouses($search = null, $city = null, $category = null)
    {
        $query = BoardingHouse::query()
            ->with(['city', 'category'])
            ->withSum('rooms', 'capacity')
            ->withSum(
                [
                    'rooms as available_rooms_sum_capacity' => function (Builder $roomQuery): void {
                        $roomQuery->where('is_available', true);
                    },
                ],
                'capacity',
            )
            ->withCount([
                'rooms as available_rooms' => function ($roomQuery) {
                    $roomQuery->where('is_available', true);
                },
            ])
            ->whereHas('rooms', function (Builder $roomQuery): void {
                $roomQuery->where('is_available', true);
            })
            ->when($search, function (Builder $query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when($city, function (Builder $query) use ($city) {
                $query->whereHas('city', function (Builder $query) use ($city) {
                    $query->where('slug', $city);
                });
            })
            ->when($category, function (Builder $query) use ($category) {
                $query->whereHas('category', function (Builder $query) use ($category) {
                    $query->where('slug', $category);
                });
            })
            ->latest()
            ->get();

        return $query;
    }

    // Menampilkan 5 kost teratas
    public function getRecommendedBoardingHouse($limit = 5, array $filters = [])
    {
        $priceMax = is_numeric($filters['price_max'] ?? null)
            ? (float) $filters['price_max']
            : null;
        $distanceMax = is_numeric($filters['distance_max'] ?? null)
            ? max((float) $filters['distance_max'], 0)
            : null;
        $ratingMin = is_numeric($filters['rating_min'] ?? null)
            ? max((float) $filters['rating_min'], 0)
            : null;

        $facilityIds = collect($filters['facilities'] ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $boardingHouses = BoardingHouse::query()
            ->with(['city', 'category', 'testimonials', 'facilities:id,name,icon'])
            ->withCount(['transactions', 'testimonials'])
            ->withSum('facilities as facilities_weight_sum', 'weight')
            ->withAvg('testimonials', 'rating')
            ->withSum('rooms', 'capacity')
            ->withSum(
                [
                    'rooms as available_rooms_sum_capacity' => function (Builder $roomQuery): void {
                        $roomQuery->where('is_available', true);
                    },
                ],
                'capacity',
            )
            ->withCount([
                'rooms as available_rooms' => function ($query) {
                    $query->where('is_available', true);
                },
            ])
            ->whereHas('rooms', function (Builder $roomQuery): void {
                $roomQuery->where('is_available', true);
            })
            ->when($priceMax !== null, function (Builder $query) use ($priceMax) {
                $query->where('price', '<=', $priceMax);
            })
            ->when($facilityIds->isNotEmpty(), function (Builder $query) use ($facilityIds) {
                $query->whereHas(
                    'facilities',
                    function (Builder $facilityQuery) use ($facilityIds) {
                        $facilityQuery->whereIn('facilities.id', $facilityIds->all());
                    },
                    '>=',
                    $facilityIds->count(),
                );
            })
            ->get();

        if ($ratingMin !== null) {
            $boardingHouses = $boardingHouses
                ->filter(function (BoardingHouse $boardingHouse) use ($ratingMin) {
                    return (float) ($boardingHouse->testimonials_avg_rating ?? 0) >= $ratingMin;
                })
                ->values();
        }

        if ($distanceMax !== null) {
            $boardingHouses = $boardingHouses
                ->filter(function (BoardingHouse $boardingHouse) use ($distanceMax) {
                    $distance = $this->resolveDistanceMetric($boardingHouse);

                    return is_numeric($distance) && (float) $distance <= $distanceMax;
                })
                ->values();
        }

        if ($boardingHouses->isEmpty()) {
            return $boardingHouses;
        }

        $weightsByName = CriteriaWeight::query()
            ->with('criteria:id,name')
            ->get()
            ->filter(fn (CriteriaWeight $weight) => $weight->criteria !== null)
            ->mapWithKeys(
                fn (CriteriaWeight $weight) => [
                    mb_strtolower(trim($weight->criteria->name)) => (float) $weight->weight,
                ],
            );

        $requiredCriteria = collect(['harga', 'rating', 'fasilitas', 'jarak']);

        if ($requiredCriteria->diff($weightsByName->keys())->isNotEmpty()) {
            return $boardingHouses->sortByDesc('transactions_count')->take($limit)->values();
        }

        $metricsByBoardingHouse = $this->buildAhpMetrics($boardingHouses);

        $normalizationBoundaries = [
            'harga' => [
                'min' => $metricsByBoardingHouse->min('harga'),
                'max' => $metricsByBoardingHouse->max('harga'),
                'type' => 'cost',
            ],
            'rating' => [
                'min' => $metricsByBoardingHouse->min('rating'),
                'max' => $metricsByBoardingHouse->max('rating'),
                'type' => 'benefit',
            ],
            'fasilitas' => [
                'min' => $metricsByBoardingHouse->min('fasilitas'),
                'max' => $metricsByBoardingHouse->max('fasilitas'),
                'type' => 'benefit',
            ],
            'jarak' => [
                'min' => $metricsByBoardingHouse->min('jarak'),
                'max' => $metricsByBoardingHouse->max('jarak'),
                'type' => 'cost',
            ],
        ];

        $scoredBoardingHouses = $boardingHouses->map(function (BoardingHouse $boardingHouse) use (
            $weightsByName,
            $metricsByBoardingHouse,
            $normalizationBoundaries,
        ) {
            $metrics = $metricsByBoardingHouse->get($boardingHouse->id);

            // Calculate normalized values using AHP formula (same as KosRankingService)
            $normHarga = $this->normalizeAhpValue(
                value: (float) ($metrics['harga'] ?? 0),
                min: (float) $normalizationBoundaries['harga']['min'],
                max: null,
                type: 'cost_inverse',
            );

            $normJarak = $this->normalizeAhpValue(
                value: (float) ($metrics['jarak'] ?? 0),
                min: (float) $normalizationBoundaries['jarak']['min'],
                max: null,
                type: 'cost_inverse',
            );

            $normFasilitas = $this->normalizeAhpValue(
                value: (float) ($metrics['fasilitas'] ?? 0),
                min: null,
                max: 26, // Total weight of all facilities
                type: 'ratio_to_total',
            );

            $normRating = $this->normalizeAhpValue(
                value: (float) ($metrics['rating'] ?? 0),
                min: null,
                max: (float) $normalizationBoundaries['rating']['max'],
                type: 'ratio_to_max',
            );

            // Calculate final score using AHP weights (use rounded values to match KosRankingService)
            $score = 0.0;
            $score += ((float) $weightsByName->get('harga', 0)) * round($normHarga, 3);
            $score += ((float) $weightsByName->get('jarak', 0)) * round($normJarak, 3);
            $score += ((float) $weightsByName->get('fasilitas', 0)) * round($normFasilitas, 3);
            $score += ((float) $weightsByName->get('rating', 0)) * round($normRating, 3);

            $boardingHouse->setAttribute('ahp_score', round($score, 3));
            $boardingHouse->setAttribute(
                'computed_distance',
                round((float) ($metrics['jarak'] ?? 0), 1),
            );

            return $boardingHouse;
        });

        return $scoredBoardingHouses->sortByDesc('ahp_score')->take($limit)->values();
    }

    private function buildAhpMetrics(Collection $boardingHouses): Collection
    {
        $rawDistances = $boardingHouses
            ->map(function (BoardingHouse $boardingHouse) {
                return $this->resolveDistanceMetric($boardingHouse);
            })
            ->filter(fn ($distance) => is_numeric($distance));

        $maxDistance = $rawDistances->max() ?? 1.0;
        $fallbackDistance = $maxDistance + 1;

        return $boardingHouses->mapWithKeys(function (BoardingHouse $boardingHouse) use (
            $fallbackDistance,
        ) {
            $distance = $this->resolveDistanceMetric($boardingHouse);

            if (! is_numeric($distance) || $distance <= 0) {
                $distance = $fallbackDistance;
            }

            return [
                $boardingHouse->id => [
                    'harga' => max((float) $boardingHouse->price, 0.0001),
                    'rating' => max((float) ($boardingHouse->testimonials_avg_rating ?? 0), 0),
                    'fasilitas' => max((float) ($boardingHouse->facilities_weight_sum ?? 0), 0),
                    'jarak' => max($distance, 0.0001),
                ],
            ];
        });
    }

    private function resolveDistanceMetric(BoardingHouse $boardingHouse): ?float
    {
        if (is_numeric($boardingHouse->latitude) && is_numeric($boardingHouse->longitude)) {
            $distanceByCoordinates = $this->haversineDistance(
                lat1: self::DEFAULT_CITY_LAT,
                lon1: self::DEFAULT_CITY_LNG,
                lat2: (float) $boardingHouse->latitude,
                lon2: (float) $boardingHouse->longitude,
            );

            if ($distanceByCoordinates > 0) {
                return $distanceByCoordinates;
            }
        }

        if (is_numeric($boardingHouse->distance)) {
            return (float) $boardingHouse->distance;
        }

        return null;
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $distanceLatitude = deg2rad($lat2 - $lat1);
        $distanceLongitude = deg2rad($lon2 - $lon1);

        $haversine =
            sin($distanceLatitude / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($distanceLongitude / 2) ** 2;

        $centralAngle = 2 * atan2(sqrt($haversine), sqrt(1 - $haversine));

        return $earthRadiusKm * $centralAngle;
    }

    private function normalizeAhpValue(
        float $value,
        ?float $min,
        ?float $max,
        string $type,
    ): float {
        if ($type === 'cost_inverse') {
            // min / value (for harga and jarak)
            if ($value <= 0 || $min === null) {
                return 0;
            }

            return $min / $value;
        }

        if ($type === 'ratio_to_total') {
            // value / total (for fasilitas)
            if ($max === null || $max <= 0) {
                return 0;
            }

            return $value / $max;
        }

        if ($type === 'ratio_to_max') {
            // value / max (for rating)
            if ($max === null || $max <= 0) {
                return 0;
            }

            return $value / $max;
        }

        return 0;
    }

    // Menampilkan kost berdasarkan city yg dipilih
    public function getBoardingHouseByCitySlug($slug)
    {
        return BoardingHouse::query()
            ->with(['city', 'category'])
            ->withSum('rooms', 'capacity')
            ->withSum(
                [
                    'rooms as available_rooms_sum_capacity' => function (Builder $roomQuery): void {
                        $roomQuery->where('is_available', true);
                    },
                ],
                'capacity',
            )
            ->whereHas('rooms', function (Builder $roomQuery): void {
                $roomQuery->where('is_available', true);
            })
            ->whereHas('city', function (Builder $query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->latest()
            ->get();
    }

    // Menampilkan kost berdasarkan category yg dipilih
    public function getBoardingHouseByCategorySlug($slug)
    {
        return BoardingHouse::query()
            ->with(['city', 'category'])
            ->withSum('rooms', 'capacity')
            ->withSum(
                [
                    'rooms as available_rooms_sum_capacity' => function (Builder $roomQuery): void {
                        $roomQuery->where('is_available', true);
                    },
                ],
                'capacity',
            )
            ->whereHas('rooms', function (Builder $roomQuery): void {
                $roomQuery->where('is_available', true);
            })
            ->whereHas('category', function (Builder $query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->latest()
            ->get();
    }

    // detail kost
    public function getBoardingHouseBySlug($slug)
    {
        return BoardingHouse::query()
            ->with([
                'city',
                'category',
                'owner',
                'rooms' => function (HasMany $roomQuery): void {
                    $roomQuery->where('is_available', true)->with('roomImages');
                },
                'testimonials',
            ])
            ->withAvg('testimonials', 'rating')
            ->withSum('rooms', 'capacity')
            ->withSum(
                [
                    'rooms as available_rooms_sum_capacity' => function (Builder $roomQuery): void {
                        $roomQuery->where('is_available', true);
                    },
                ],
                'capacity',
            )
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getBestByRating($limit = 5)
    {
        return BoardingHouse::query()
            ->with(['city', 'category', 'testimonials'])
            ->withAvg('testimonials', 'rating')
            ->withCount('testimonials')
            ->withSum('rooms', 'capacity')
            ->withSum(
                [
                    'rooms as available_rooms_sum_capacity' => function (Builder $roomQuery): void {
                        $roomQuery->where('is_available', true);
                    },
                ],
                'capacity',
            )
            ->withCount([
                'rooms as available_rooms' => function ($roomQuery) {
                    $roomQuery->where('is_available', true);
                },
            ])
            ->whereHas('rooms', function (Builder $roomQuery): void {
                $roomQuery->where('is_available', true);
            })
            ->orderByDesc('testimonials_avg_rating')
            ->take($limit)
            ->get();
    }

    public function getBoardingHouseRoomById($id)
    {
        return Room::query()->whereKey($id)->where('is_available', true)->first();
    }
}