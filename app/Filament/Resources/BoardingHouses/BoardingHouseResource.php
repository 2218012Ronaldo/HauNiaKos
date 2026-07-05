<?php

namespace App\Filament\Resources\BoardingHouses;

use App\Filament\Resources\BoardingHouses\Pages\CreateBoardingHouse;
use App\Filament\Resources\BoardingHouses\Pages\EditBoardingHouse;
use App\Filament\Resources\BoardingHouses\Pages\ListBoardingHouses;
use App\Filament\Resources\BoardingHouses\Schemas\BoardingHouseForm;
use App\Filament\Resources\BoardingHouses\Tables\BoardingHousesTable;
use App\Models\BoardingHouse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BoardingHouseResource extends Resource
{
    protected static ?string $model = BoardingHouse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'BoardingHouse';

    public static function form(Schema $schema): Schema
    {
        return BoardingHouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoardingHousesTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return in_array(filament()->auth()->user()?->role, ['admin', 'owner_kost'], true);
    }

    public static function canCreate(): bool
    {
        return in_array(filament()->auth()->user()?->role, ['admin', 'owner_kost'], true);
    }

    public static function canEdit(Model $record): bool
    {
        $user = filament()->auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'owner_kost' && (int) $record->owner_id === (int) $user->id;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function canRestore(Model $record): bool
    {
        return filament()->auth()->user()?->role === 'admin';
    }

    public static function canForceDelete(Model $record): bool
    {
        return filament()->auth()->user()?->role === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(
            filament()->auth()->user()?->role === 'owner_kost',
            fn (Builder $query) => $query->where('owner_id', filament()->auth()->id()),
        );
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
            'index' => ListBoardingHouses::route('/'),
            'create' => CreateBoardingHouse::route('/create'),
            'edit' => EditBoardingHouse::route('/{record}/edit'),
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
