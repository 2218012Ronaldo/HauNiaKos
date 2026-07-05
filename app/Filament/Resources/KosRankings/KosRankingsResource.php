<?php

namespace App\Filament\Resources\KosRankings;

use App\Filament\Resources\KosRankings\Pages\ListKosRankings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KosRankingsResource extends Resource
{
    protected static ?string $model = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = 'AHP';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Peringkat Kos';

    protected static ?string $pluralModelLabel = 'Peringkat Kos';

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\KosRankings\Tables\KosRankingsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return in_array(filament()->auth()->user()?->role, ['admin']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKosRankings::route('/'),
        ];
    }
}