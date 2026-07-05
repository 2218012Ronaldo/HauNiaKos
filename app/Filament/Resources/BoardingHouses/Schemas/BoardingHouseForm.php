<?php

namespace App\Filament\Resources\BoardingHouses\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BoardingHouseForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema->components([
            Tabs::make('Tabs')
                ->tabs([
                    Tab::make('General information')->schema([
                        FileUpload::make('thumbnail')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->directory('boarding_house')
                            ->imagePreviewHeight('300px'),
                        TextInput::make('name')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')->required()->readOnly(),
                        Select::make('city_id')->relationship('city', 'name')->required(),
                        Select::make('category_id')->relationship('category', 'name')->required(),
                        Select::make('gender_type')
                            ->options(['male' => 'Male', 'female' => 'Female', 'mixed' => 'Mixed'])
                            ->default('mixed'),
                        Select::make('owner_id')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->visible(fn (): bool => filament()->auth()->user()?->role === 'admin')
                            ->required(fn (): bool => filament()->auth()->user()?->role === 'admin'),
                        RichEditor::make('description')
                            ->required()
                            ->extraAttributes(['style' => 'min-height:300px;'])
                            ->columnSpanFull(),
                        RichEditor::make('rules')
                            ->required()
                            ->extraAttributes(['style' => 'min-height:300px;'])
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->label('Price (USD)'),
                        Textarea::make('address')->required()->columnSpanFull(),
                    ]),

                    Tab::make('Bonus')->schema([
                        Repeater::make('bonuses')
                            ->relationship('bonuses')
                            ->schema([
                                FileUpload::make('image')
                                    ->required()
                                    ->image()
                                    ->disk('public')
                                    ->directory('bonuses')
                                    ->imagePreviewHeight('300px'),
                                TextInput::make('name')->required(),
                                TextInput::make('description')->required(),
                            ]),
                    ]),

                    Tab::make('Facility')->schema([
                        CheckboxList::make('facilities')
                            ->relationship('facilities', 'name')
                            ->columns(3),
                    ]),
                    Tab::make('Room')->schema([
                        Repeater::make('rooms')
                            ->relationship('rooms')
                            ->itemLabel(function (array $state): ?string {
                                if (! isset($state['name'])) {
                                    return null;
                                }

                                $availabilityLabel = (bool) ($state['is_available'] ?? true)
                                    ? 'Available'
                                    : 'Unavailable';

                                return sprintf('%s • %s', $state['name'], $availabilityLabel);
                            })
                            ->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('room_type')->required(),
                                TextInput::make('square_feet')->numeric()->required(),
                                TextInput::make('capacity')->numeric()->required(),
                               TextInput::make('price_per_month')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->label('Price per Month (USD)')
                                    ->required(),
                                Toggle::make('is_available')->required(),
                                Repeater::make('roomImages')
                                    ->relationship('roomImages')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->required()
                                            ->image()
                                            ->disk('public')
                                            ->directory('rooms')
                                            ->imagePreviewHeight('300px'),
                                    ]),
                            ]),
                    ]),
                    Tab::make('Location')->schema([
                        Field::make('map_field')
                            ->label('')
                            ->view('components.map-picker')
                            ->viewData(
                                fn(string $operation) => ['disabled' => $operation === 'view'],
                            )
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->reactive()
                            ->afterStateHydrated(fn() => null)
                            ->disabled(fn(string $operation): bool => $operation === 'view'),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->required()
                            ->live(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->required()
                            ->live(),
                    ]),
                ])
                ->columnSpan(2),
        ]);
    }
}
