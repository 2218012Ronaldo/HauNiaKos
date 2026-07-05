<?php

namespace App\Filament\Resources\BoardingHouses\Pages;

use App\Filament\Resources\BoardingHouses\BoardingHouseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBoardingHouse extends EditRecord
{
    protected static string $resource = BoardingHouseResource::class;

     protected function getRedirectUrl(): string
    {
        // Always redirect to the resource index (cities list) after create
        return $this->getResourceUrl();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    // 🔹 Tambahkan listener Livewire untuk map picker
    protected function getListeners(): array
    {
        return [
            'setLocation' => 'setLocation',
        ];
    }

    // 🔹 Method yang akan dipanggil JS via Livewire.dispatch
    public function setLocation(?array $data = null): void
    {
        if (! is_array($data) || ! isset($data['lat'], $data['lng'])) {
            return;
        }

        $this->form->fill([
            'latitude' => $data['lat'],
            'longitude' => $data['lng'],
        ]);
    }
}
