<?php

namespace App\Filament\Resources\BoardingHouses\Tables;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Image;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BoardingHousesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                ImageColumn::make('thumbnail')->disk('public')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('City')
                    ->sortable(),
                TextColumn::make('Category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('gender_type')
                    ->badge(),
                TextColumn::make('rooms_count')
                    ->label('Rooms')
                    ->getStateUsing(fn ($record) => $record->rooms()->count())
                    ->numeric(),
                    TextColumn::make('available_rooms')
                    ->label('Availability')
                    ->getStateUsing(
                        fn ($record) => $record->rooms()->where('is_available', true)->count(),
                    )
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->formatStateUsing(
                        fn (int $state): string => $state > 0
                            ? "{$state} room(s) available"
                            : 'Fully booked',
                    ),
                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable(),
                TextColumn::make('distance')
                    ->numeric()
                    ->sortable()->hidden(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
