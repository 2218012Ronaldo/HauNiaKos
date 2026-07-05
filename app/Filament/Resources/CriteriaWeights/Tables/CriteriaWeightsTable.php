<?php

namespace App\Filament\Resources\CriteriaWeights\Tables;

use App\Models\CriteriaWeight;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CriteriaWeightsTable
{
    /**
     * @var array<int, float>|null
     */
    private static ?array $roundedPercentagesById = null;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('criteria.name')->label('Criteria')->searchable(),
                TextColumn::make('weight')
                    ->label('Weight')
                    ->formatStateUsing(function (float $state, CriteriaWeight $record): string {
                        $roundedPercentages = self::getRoundedPercentagesById();
                        $percentage = $roundedPercentages[$record->id] ?? round($state * 100, 1);

                        return number_format($percentage, 1).'%';
                    })
                    ->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    /**
     * @return array<int, float>
     */
    private static function getRoundedPercentagesById(): array
    {
        if (self::$roundedPercentagesById !== null) {
            return self::$roundedPercentagesById;
        }

        $records = CriteriaWeight::query()
            ->orderBy('id')
            ->get(['id', 'weight']);

        if ($records->isEmpty()) {
            self::$roundedPercentagesById = [];

            return self::$roundedPercentagesById;
        }

        $rows = [];
        $flooredTotal = 0.0;

        foreach ($records as $record) {
            $rawPercentage = ((float) $record->weight) * 100;
            $flooredPercentage = floor($rawPercentage * 10 + 1e-9) / 10;
            $remainder = $rawPercentage - $flooredPercentage;

            $rows[] = [
                'id' => (int) $record->id,
                'value' => $flooredPercentage,
                'remainder' => $remainder,
            ];

            $flooredTotal += $flooredPercentage;
        }

        $neededTenthUnits = (int) round((100.0 - $flooredTotal) * 10);

        if ($neededTenthUnits > 0) {
            usort($rows, static function (array $left, array $right): int {
                if ($left['remainder'] === $right['remainder']) {
                    return $left['id'] <=> $right['id'];
                }

                return $right['remainder'] <=> $left['remainder'];
            });

            for ($index = 0; $index < $neededTenthUnits; $index++) {
                $rowIndex = $index % count($rows);
                $rows[$rowIndex]['value'] = round($rows[$rowIndex]['value'] + 0.1, 1);
            }
        } elseif ($neededTenthUnits < 0) {
            usort($rows, static function (array $left, array $right): int {
                if ($left['remainder'] === $right['remainder']) {
                    return $left['id'] <=> $right['id'];
                }

                return $left['remainder'] <=> $right['remainder'];
            });

            for ($index = 0; $index < abs($neededTenthUnits); $index++) {
                $rowIndex = $index % count($rows);

                if ($rows[$rowIndex]['value'] >= 0.1) {
                    $rows[$rowIndex]['value'] = round($rows[$rowIndex]['value'] - 0.1, 1);
                }
            }
        }

        $mappedPercentages = [];

        foreach ($rows as $row) {
            $mappedPercentages[$row['id']] = round((float) $row['value'], 1);
        }

        self::$roundedPercentagesById = $mappedPercentages;

        return self::$roundedPercentagesById;
    }
}
