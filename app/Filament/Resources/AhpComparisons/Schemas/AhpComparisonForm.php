<?php

namespace App\Filament\Resources\AhpComparisons\Schemas;

use App\Models\Criteria;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AhpComparisonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('criteria_id_1')
                ->label('Criteria 1')
                ->relationship('criteriaOne', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('criteria_id_2')
                ->label('Criteria 2')
                ->options(
                    fn (Get $get): array => Criteria::query()
                        ->when(
                            $get('criteria_id_1'),
                            fn ($query) => $query->where('id', '!=', $get('criteria_id_1')),
                        )
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray(),
                )
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('value')
                ->label('Pairwise Value')
                ->numeric()
                ->required()
                ->minValue(0.111)
                ->maxValue(9)
                ->step(0.001)
                ->helperText('Use Saaty scale (1-9). Reciprocal values are allowed (e.g. 0.333).'),
        ]);
    }
}
