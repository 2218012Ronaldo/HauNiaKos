<?php

namespace App\Filament\Resources\CriteriaWeights\Pages;

use App\Filament\Resources\CriteriaWeights\CriteriaWeightResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCriteriaWeight extends EditRecord
{
    protected static string $resource = CriteriaWeightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
