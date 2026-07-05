<?php

namespace App\Filament\Resources\AhpComparisons\Pages;

use App\Filament\Resources\AhpComparisons\AhpComparisonResource;
use App\Models\AhpComparison;
use App\Services\AhpWeightCalculator;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class EditAhpComparison extends EditRecord
{
    protected static string $resource = AhpComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->after(function (): void {
                try {
                    app(AhpWeightCalculator::class)->calculateAndStore();

                    Notification::make()
                        ->title('AHP weights recalculated successfully.')
                        ->success()
                        ->send();
                } catch (RuntimeException $exception) {
                    Notification::make()
                        ->title('Comparison deleted, but recalculation failed.')
                        ->body($exception->getMessage())
                        ->warning()
                        ->send();
                }
            }),
        ];
    }

    protected function afterSave(): void
    {
        try {
            app(AhpWeightCalculator::class)->calculateAndStore();

            Notification::make()
                ->title('AHP weights recalculated successfully.')
                ->success()
                ->send();
        } catch (RuntimeException $exception) {
            Notification::make()
                ->title('Comparison updated, but recalculation failed.')
                ->body($exception->getMessage())
                ->warning()
                ->send();
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $criteriaIdOne = (int) $data['criteria_id_1'];
        $criteriaIdTwo = (int) $data['criteria_id_2'];
        $value = (float) $data['value'];

        if ($criteriaIdOne === $criteriaIdTwo) {
            throw ValidationException::withMessages([
                'criteria_id_2' => 'Criteria 2 must be different from Criteria 1.',
            ]);
        }

        if ($value <= 0) {
            throw ValidationException::withMessages([
                'value' => 'Pairwise value must be greater than zero.',
            ]);
        }

        if ($criteriaIdOne > $criteriaIdTwo) {
            [$criteriaIdOne, $criteriaIdTwo] = [$criteriaIdTwo, $criteriaIdOne];
            $value = 1 / $value;
        }

        $isPairExists = AhpComparison::query()
            ->where('criteria_id_1', $criteriaIdOne)
            ->where('criteria_id_2', $criteriaIdTwo)
            ->whereKeyNot($this->record->getKey())
            ->exists();

        if ($isPairExists) {
            throw ValidationException::withMessages([
                'criteria_id_2' => 'This criteria pair already exists.',
            ]);
        }

        $data['criteria_id_1'] = $criteriaIdOne;
        $data['criteria_id_2'] = $criteriaIdTwo;
        $data['value'] = round($value, 3);

        return $data;
    }
}
