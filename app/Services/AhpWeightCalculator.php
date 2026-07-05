<?php

namespace App\Services;

use App\Models\AhpComparison;
use App\Models\Criteria;
use App\Models\CriteriaWeight;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AhpWeightCalculator
{
    /**
     * @return array{weights: array<int, array{criteria_id:int, weight:float}>, criteria_count:int, pair_count:int}
     */
    public function calculateAndStore(): array
    {
        $criteria = Criteria::query()
            ->orderBy('id')
            ->get(['id']);

        $criteriaIds = $criteria->pluck('id')->all();
        $criteriaCount = count($criteriaIds);

        if ($criteriaCount < 2) {
            throw new RuntimeException('Minimal 2 criteria dibutuhkan untuk menghitung bobot AHP.');
        }

        $expectedPairCount = (int) (($criteriaCount * ($criteriaCount - 1)) / 2);

        $idToIndex = [];

        foreach ($criteriaIds as $index => $criteriaId) {
            $idToIndex[$criteriaId] = $index;
        }

        $matrix = [];

        for ($row = 0; $row < $criteriaCount; $row++) {
            $matrix[$row] = array_fill(0, $criteriaCount, null);
            $matrix[$row][$row] = 1.0;
        }

        $comparisons = AhpComparison::query()
            ->whereIn('criteria_id_1', $criteriaIds)
            ->whereIn('criteria_id_2', $criteriaIds)
            ->get(['criteria_id_1', 'criteria_id_2', 'value']);

        foreach ($comparisons as $comparison) {
            $criteriaIdOne = (int) $comparison->criteria_id_1;
            $criteriaIdTwo = (int) $comparison->criteria_id_2;
            $value = (float) $comparison->value;

            if ($value <= 0) {
                throw new RuntimeException('Nilai pairwise harus lebih besar dari 0.');
            }

            if (! isset($idToIndex[$criteriaIdOne], $idToIndex[$criteriaIdTwo])) {
                continue;
            }

            $row = $idToIndex[$criteriaIdOne];
            $column = $idToIndex[$criteriaIdTwo];

            $matrix[$row][$column] = $value;
            $matrix[$column][$row] = 1 / $value;
        }

        $pairCount = 0;

        for ($row = 0; $row < $criteriaCount; $row++) {
            for ($column = $row + 1; $column < $criteriaCount; $column++) {
                if ($matrix[$row][$column] === null || $matrix[$column][$row] === null) {
                    throw new RuntimeException(
                        'Perbandingan pairwise belum lengkap. Isi semua pasangan kriteria terlebih dahulu.',
                    );
                }

                $pairCount++;
            }
        }

        if ($pairCount !== $expectedPairCount) {
            throw new RuntimeException('Jumlah pasangan pairwise belum sesuai.');
        }

        $columnSums = array_fill(0, $criteriaCount, 0.0);

        for ($column = 0; $column < $criteriaCount; $column++) {
            for ($row = 0; $row < $criteriaCount; $row++) {
                $columnSums[$column] += (float) $matrix[$row][$column];
            }

            if ($columnSums[$column] <= 0) {
                throw new RuntimeException(
                    'Kolom matriks memiliki jumlah 0, periksa kembali nilai pairwise.',
                );
            }
        }

        $weights = array_fill(0, $criteriaCount, 0.0);

        for ($row = 0; $row < $criteriaCount; $row++) {
            $rowSum = 0.0;

            for ($column = 0; $column < $criteriaCount; $column++) {
                $rowSum += ((float) $matrix[$row][$column]) / $columnSums[$column];
            }

            $weights[$row] = $rowSum / $criteriaCount;
        }

        $weightTotal = array_sum($weights);

        if ($weightTotal <= 0) {
            throw new RuntimeException('Total bobot tidak valid.');
        }

        $normalizedWeights = array_map(
            fn (float $weight): float => $weight / $weightTotal,
            $weights,
        );

        $roundedWeights = array_map(
            static fn (float $weight): float => round($weight, 4),
            $normalizedWeights,
        );

        $roundedTotal = round(array_sum($roundedWeights), 4);
        $roundingDelta = round(1 - $roundedTotal, 4);

        if ($roundingDelta !== 0.0) {
            $targetIndex = array_key_first($roundedWeights);

            foreach ($roundedWeights as $index => $weight) {
                if ($targetIndex === null || $weight > $roundedWeights[$targetIndex]) {
                    $targetIndex = $index;
                }
            }

            if ($targetIndex !== null) {
                $roundedWeights[$targetIndex] = round(
                    $roundedWeights[$targetIndex] + $roundingDelta,
                    4,
                );
            }
        }

        $storedWeights = [];

        DB::transaction(function () use ($criteriaIds, $roundedWeights, &$storedWeights): void {
            CriteriaWeight::query()->whereNotIn('criteria_id', $criteriaIds)->delete();

            foreach ($criteriaIds as $index => $criteriaId) {
                $storedWeight = $roundedWeights[$index];

                CriteriaWeight::query()->updateOrCreate(
                    ['criteria_id' => $criteriaId],
                    ['weight' => $storedWeight],
                );

                $storedWeights[] = [
                    'criteria_id' => $criteriaId,
                    'weight' => $storedWeight,
                ];
            }
        });

        return [
            'weights' => $storedWeights,
            'criteria_count' => $criteriaCount,
            'pair_count' => $pairCount,
        ];
    }
}
