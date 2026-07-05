<?php

namespace App\Filament\Resources\Criterias;

use App\Filament\Resources\Criterias\Pages\CreateCriteria;
use App\Filament\Resources\Criterias\Pages\EditCriteria;
use App\Filament\Resources\Criterias\Pages\ListCriterias;
use App\Filament\Resources\Criterias\Schemas\CriteriaForm;
use App\Filament\Resources\Criterias\Tables\CriteriasTable;
use App\Models\Criteria;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CriteriaResource extends Resource
{
    protected static ?string $model = Criteria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'AHP';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Criteria';

    protected static ?string $pluralModelLabel = 'Criteria';

    protected static ?string $recordTitleAttribute= 'name';
    
    public static function form(Schema $schema): Schema
    {
        return CriteriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CriteriasTable::configure($table);
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
            'index' => ListCriterias::route('/'),
            'create' => CreateCriteria::route('/create'),
            'edit' => EditCriteria::route('/{record}/edit'),
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
