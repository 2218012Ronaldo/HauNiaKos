<?php

namespace App\Filament\Resources\BoardingHouses\Pages;

use App\Filament\Resources\BoardingHouses\BoardingHouseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoardingHouses extends ListRecords
{
    protected static string $resource = BoardingHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
