<?php

namespace App\Filament\Resources\CriteriaWeights;

use App\Filament\Resources\CriteriaWeights\Pages\CreateCriteriaWeight;
use App\Filament\Resources\CriteriaWeights\Pages\EditCriteriaWeight;
use App\Filament\Resources\CriteriaWeights\Pages\ListCriteriaWeights;
use App\Filament\Resources\CriteriaWeights\Schemas\CriteriaWeightForm;
use App\Filament\Resources\CriteriaWeights\Tables\CriteriaWeightsTable;
use App\Models\CriteriaWeight;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CriteriaWeightResource extends Resource
{
    protected static ?string $model = CriteriaWeight::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'AHP';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Criteria Weight';

    protected static ?string $pluralModelLabel = 'Criteria Weights';

    public static function form(Schema $schema): Schema
    {
        return CriteriaWeightForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CriteriaWeightsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return in_array(filament()->auth()->user()?->role, ['admin']);
            
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCriteriaWeights::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
