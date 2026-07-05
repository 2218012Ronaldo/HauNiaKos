<?php

namespace App\Filament\Resources\AhpComparisons\Pages;

use App\Filament\Resources\AhpComparisons\AhpComparisonResource;
use App\Services\AhpWeightCalculator;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use RuntimeException;

class ListAhpComparisons extends ListRecords
{
    protected static string $resource = AhpComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('recalculate')
                ->label('Recalculate Weights')
                ->icon('heroicon-o-arrow-path')
                ->action(function (): void {
                    try {
                        app(AhpWeightCalculator::class)->calculateAndStore();

                        Notification::make()
                            ->title('AHP weights recalculated successfully.')
                            ->success()
                            ->send();
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