<?php

namespace App\Filament\Resources\KosRankings\Pages;

use App\Filament\Resources\KosRankings\KosRankingsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKosRankings extends EditRecord
{
    protected static string $resource = KosRankingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
