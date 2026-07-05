<?php

namespace App\Filament\Resources\KosRankings\Pages;

use App\Filament\Resources\KosRankings\KosRankingsResource;
use App\Filament\Resources\KosRankings\Schemas\KosRankingsForm;
use Filament\Resources\Pages\ListRecords;

class ListKosRankings extends ListRecords
{
    protected static string $resource = KosRankingsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}