<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Room;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TransactionsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required(),
            Select::make('boarding_house_id')
                ->relationship(
                    name: 'boardingHouse',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query->when(
                        filament()->auth()->user()?->role === 'owner_kost',
                        fn (Builder $boardingHouseQuery) => $boardingHouseQuery->where(
                            'owner_id',
                            filament()->auth()->id(),
                        ),
                    ),
                )
                ->searchable()
                ->required(),
            Select::make('room_id')
                ->relationship(
                    name: 'room',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query
                        ->where('is_available', true)
                        ->when(
                            filament()->auth()->user()?->role === 'owner_kost',
                            fn (Builder $roomQuery) => $roomQuery->whereHas(
                                'boardingHouse',
                                fn (Builder $boardingHouseQuery) => $boardingHouseQuery->where(
                                    'owner_id',
                                    filament()->auth()->id(),
                                ),
                            ),
                        ),
                )
                ->getOptionLabelFromRecordUsing(
                    fn (Room $record) => sprintf(
                        '%s • %s • Rp%s',
                        $record->name,
                        $record->availabilityLabel(),
                        number_format($record->price_per_month, 0, ',', '.'),
                    ),
                )
                ->searchable()
                ->required(),
            TextInput::make('name')->required(),
            TextInput::make('email')->label('Email address')->email()->required(),
            TextInput::make('phone_number')->tel()->required(),
            Select::make('gender')->options([
                'male' => 'Male',
                'female' => 'Female',
            ])->required(),
            Select::make('payment_method')->options([
                'down_payment' => 'Down payment',
                'full_payment' => 'Full payment',
            ]),
            Select::make('payment_status')->options([
                'pending' => 'Pending',
                'paid' => 'Paid',
            ]),
            DatePicker::make('start_date')->required(),
            TextInput::make('duration')->required()->numeric(),
            TextInput::make('total_amount')->numeric()->prefix('IDR'),
            DatePicker::make('transaction_date'),
        ]);
    }
}
