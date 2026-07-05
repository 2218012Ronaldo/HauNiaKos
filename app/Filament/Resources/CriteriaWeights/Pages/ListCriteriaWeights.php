<?php

namespace App\Filament\Resources\CriteriaWeights\Pages;

use App\Filament\Resources\CriteriaWeights\CriteriaWeightResource;
use App\Services\AhpWeightCalculator;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use RuntimeException;

class ListCriteriaWeights extends ListRecords
{
    protected static string $resource = CriteriaWeightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action  ::make('recalculate')
                ->label('Recalculate Weights')
                ->icon('heroicon-o-arrow-path')
                ->action(function (): void {
                    try {
                        app(AhpWeightCalculator::class)->calculateAndStore();

                        Notification::make()
                            ->title('AHP weights recalculated successfully.')
                            ->success()
                            ->send();

                        $this->resetTable();
                    } catch (RuntimeException $exception) {
                        Notification::make()
                            ->title('Recalculation failed.')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
