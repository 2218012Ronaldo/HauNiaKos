<?php

namespace App\Services;

use App\Models\BoardingHouse;
use App\Models\CriteriaWeight;
use App\Models\Facility;
use Illuminate\Support\Collection;

class KosRankingService
{
    private const DEFAULT_CITY_LAT = -8.558611;

    private const DEFAULT_CITY_LNG = 125.573056;

    /**
     * Calculate rankings for all boarding houses using AHP method
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function calculateRankings(): Collection
    {
        $boardingHouses = BoardingHouse::query()
            ->with(['city', 'category', 'testimonials'])
            ->withSum('facilities as facilities_weight_sum', 'weight')
            ->withAvg('testimonials', 'rating')
            ->get(['id', 'name', 'price', 'distance', 'latitude', 'longitude']);

        // Load facilities for each boarding house separately to ensure they're loaded
        $boardingHouses->each(function ($boardingHouse) {
            $boardingHouse->load('facilities');
        });

        $facilities = Facility::all(['id', 'name', 'weight']);

        // Get weights from database (CriteriaWeight) - already in decimal format
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
            return collect();
        }

        // Build metrics for normalization using raw distance values
        $metricsByBoardingHouse = $this->buildAhpMetrics($boardingHouses);

        // Calculate normalization boundaries
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

        // Calculate rankings with AHP normalization
        $rankings = $boardingHouses->map(function (BoardingHouse $boardingHouse) use (
            $facilities,
            $weightsByName,
            $metricsByBoardingHouse,
            $normalizationBoundaries,
        ) {
            $metrics = $metricsByBoardingHouse->get($boardingHouse->id);

            // Calculate facility scores (use weight from facilities table if present)
            $facilityScores = [];
            $totalFacilityWeight = 0;
            foreach ($facilities as $facility) {
                $hasFacility = $boardingHouse->facilities->contains('id', $facility->id);
                $weight = $hasFacility ? (float) ($facility->weight ?? 0) : 0;
                $facilityScores[$facility->name] = $weight;
                $totalFacilityWeight += $weight;
            }

            // Calculate normalized values using AHP formula
            $normHarga = $this->normalizeAhpValue(
                (float) ($metrics['harga'] ?? 0),
                (float) $normalizationBoundaries['harga']['min'],
                null,
                'cost_inverse',
            );

            // Use raw distance metric values for jarak normalization
            $normJarak = $this->normalizeAhpValue(
                (float) ($metrics['jarak'] ?? 0),
                (float) $normalizationBoundaries['jarak']['min'],
                null,
                'cost_inverse',
            );

            $normFasilitas = $this->normalizeAhpValue(
                (float) ($metrics['fasilitas'] ?? 0),
                null,
                26, // Total weight of all facilities
                'ratio_to_total',
            );

            $normRating = $this->normalizeAhpValue(
                (float) ($metrics['rating'] ?? 0),
                null,
                (float) $normalizationBoundaries['rating']['max'],
                'ratio_to_max',
            );

            // Calculate final score using AHP weights (use rounded values to match Excel)
            $score = 0.0;
            $score += ((float) ($weightsByName['harga'] ?? 0)) * round($normHarga, 3);
            $score += ((float) ($weightsByName['jarak'] ?? 0)) * round($normJarak, 3);
            $score += ((float) ($weightsByName['fasilitas'] ?? 0)) * round($normFasilitas, 3);
            $score += ((float) ($weightsByName['rating'] ?? 0)) * round($normRating, 3);

            return [
                'boarding_house_id' => $boardingHouse->id,
                'name' => $boardingHouse->name,
                'price' => $boardingHouse->price,
                'distance' => round((float) ($metrics['jarak'] ?? 0), 1),
                'rating' => round((float) ($metrics['rating'] ?? 0), 2),
                'facility_scores' => $facilityScores,
                'total_facility_score' => $totalFacilityWeight,
                'norm_harga' => round($normHarga, 3),
                'norm_jarak' => round($normJarak, 3),
                'norm_fasilitas' => round($normFasilitas, 3),
                'norm_rating' => round($normRating, 3),
                'final_score' => round($score, 3),
            ];
        });

        // Sort by final score descending and assign ranks
        $rankings = $rankings->sortByDesc('final_score')->values();

        $rankings = $rankings->map(function (array $ranking, int $index) {
            $ranking['rank'] = $index + 1;

            return $ranking;
        });

        return $rankings;
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

            $distance = max($distance, 0.0001);
            $distance = round($distance, 1); // use displayed distance values for normalization

            return [
                $boardingHouse->id => [
                    'harga' => max((float) $boardingHouse->price, 0.0001),
                    'rating' => max((float) ($boardingHouse->testimonials_avg_rating ?? 0), 0),
                    'fasilitas' => max((float) ($boardingHouse->facilities_weight_sum ?? 0), 0),
                    'jarak' => $distance,
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
}