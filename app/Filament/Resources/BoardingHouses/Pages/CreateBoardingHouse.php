<?php

namespace App\Filament\Resources\BoardingHouses\Pages;

use App\Filament\Resources\BoardingHouses\BoardingHouseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBoardingHouse extends CreateRecord
{
    protected static string $resource = BoardingHouseResource::class;

    protected function getRedirectUrl(): string
    {
        // Always redirect to the resource index (cities list) after create
        return $this->getResourceUrl();
    }

    // Accept direct Livewire updates to these keys if the frontend sets them
    public ?float $latitude = null;

    public ?float $longitude = null;

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

    /**
     * Ensure latitude/longitude are present on the top-level payload
     * if the form stores them in a nested `data` array (e.g. data.data.0.latitude).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filament()->auth()->user()?->role === 'owner_kost') {
            $data['owner_id'] = filament()->auth()->id();
        }

        $state = $this->form->getState();
        // If Livewire updated top-level public properties directly, prefer them
        if (
            (empty($data['latitude']) || empty($data['longitude'])) &&
            (is_numeric($this->latitude) && is_numeric($this->longitude))
        ) {
            $data['latitude'] = $this->latitude;
            $data['longitude'] = $this->longitude;
        }

        // Otherwise, try to find them anywhere in the nested form state
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $found = $this->findLatLngInState($state);
            if ($found) {
                $data['latitude'] = $found['lat'];
                $data['longitude'] = $found['lng'];
            }
        }

        // Log final payload for debugging (temporary)
        try {
            \Illuminate\Support\Facades\Log::debug(
                'CreateBoardingHouse::mutateFormDataBeforeCreate',
                $data,
            );
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        return $data;
    }

    protected function findLatLngInState(array $state): ?array
    {
        foreach ($state as $key => $value) {
            if ($key === 'latitude' && is_numeric($value)) {
                $lat = $value;
                // try to find longitude nearby
                if (isset($state['longitude']) && is_numeric($state['longitude'])) {
                    return ['lat' => $lat, 'lng' => $state['longitude']];
                }
            }
            if ($key === 'longitude' && is_numeric($value)) {
                $lng = $value;
                if (isset($state['latitude']) && is_numeric($state['latitude'])) {
                    return ['lat' => $state['latitude'], 'lng' => $lng];
                }
            }
            if (is_array($value)) {
                $res = $this->findLatLngInState($value);
                if ($res) {
                    return $res;
                }
            }
        }

        return null;
    }
}
