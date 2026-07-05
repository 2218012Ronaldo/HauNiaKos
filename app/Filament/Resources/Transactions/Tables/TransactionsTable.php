<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable(),
                TextColumn::make('boardingHouse.name')->label('Boarding House')->searchable(),
                TextColumn::make('room.name')->label('Room')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->label('Email address')->searchable(),
                TextColumn::make('phone_number')->searchable(),
                TextColumn::make('gender')->badge()->formatStateUsing(
                    fn(?string $state): ?string => $state ? ucfirst($state) : null,
                 ), 
                // TextColumn::make('payment_method')->badge(),
                TextColumn::make('approval_status')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): ?string => $state
                            ? str_replace('_', ' ', ucfirst($state))
                            : null,
                    ),
                TextColumn::make('payment_status')->searchable(),
                TextColumn::make('payment_method')->badge('orange')->formatStateUsing(
                        fn (?string $state): ?string => $state
                            ? str_replace('_', ' ', ucfirst($state))
                            : null,
                    )->searchable(),
                TextColumn::make('start_date')->date()->sortable(),
                TextColumn::make('duration')->numeric()->sortable(),
               TextColumn::make('total_amount')
                    ->numeric()
                    ->formatStateUsing(function ($state, $record) {
                        $amount = $record->payment_method === 'down_payment'
                            ? $state * 0.3
                            : $state;
                        return '$' . number_format($amount, 2, '.', ',');
                    })
                    ->sortable(),
                TextColumn::make('transaction_date')->date()->sortable(),
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
            ->filters([TrashedFilter::make()])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn (Transaction $record): bool => $record->isPendingOwner())
                    ->action(function (Transaction $record): void {
                        $record->approve();
                        \App\Models\NotificationFeed::recordBookingApproved($record);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn (Transaction $record): bool => $record->isPendingOwner())
                    ->action(function (Transaction $record): void {
                        $record->reject();
                        \App\Models\NotificationFeed::recordBookingRejected($record);
                    }),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
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
