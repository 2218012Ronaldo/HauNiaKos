<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransactions;
use App\Filament\Resources\Transactions\Pages\EditTransactions;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Schemas\TransactionsForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Transaction';

    public static function form(Schema $schema): Schema
    {
        return TransactionsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
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

        return $user->role === 'owner_kost' &&
            (int) optional($record->boardingHouse)->owner_id === (int) $user->id;
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
        $query = parent::getEloquentQuery();

        if (filament()->auth()->user()?->role === 'owner_kost') {
            return $query->whereHas('boardingHouse', function (Builder $boardingHouseQuery): void {
                $boardingHouseQuery->where('owner_id', filament()->auth()->id());
            });
        }

        // Admin dapat melihat semua records termasuk soft deleted
        if (filament()->auth()->user()?->role === 'admin') {
            return $query->withTrashed();
        }

        return $query;
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransactions::route('/create'),
            'edit' => EditTransactions::route('/{record}/edit'),
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