<?php

namespace App\Filament\Resources\AhpComparisons;

use App\Filament\Resources\AhpComparisons\Pages\CreateAhpComparison;
use App\Filament\Resources\AhpComparisons\Pages\EditAhpComparison;
use App\Filament\Resources\AhpComparisons\Pages\ListAhpComparisons;
use App\Filament\Resources\AhpComparisons\Schemas\AhpComparisonForm;
use App\Filament\Resources\AhpComparisons\Tables\AhpComparisonsTable;
use App\Models\AhpComparison;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AhpComparisonResource extends Resource
{
    protected static ?string $model = AhpComparison::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

     protected static string |UnitEnum|null $navigationGroup = 'AHP';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'AHP Comparison';

    protected static ?string $pluralModelLabel = 'AHP Comparisons';

    public static function form(Schema $schema): Schema
    {
        return AhpComparisonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AhpComparisonsTable::configure($table);
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
            'index' => ListAhpComparisons::route('/'),
            'create' => CreateAhpComparison::route('/create'),
            'edit' => EditAhpComparison::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
