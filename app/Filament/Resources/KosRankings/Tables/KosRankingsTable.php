<?php

namespace App\Filament\Resources\KosRankings\Tables;

use App\Models\Facility;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KosRankingsTable
{
    public static function configure(Table $table): Table
    {
        $facilities = Facility::all(['id', 'name']);
        $rankingService = new \App\Services\KosRankingService;
        $rankings = $rankingService->calculateRankings()->keyBy('boarding_house_id');

        // Get boarding house IDs in ranking order
        $rankedIds = $rankings->pluck('boarding_house_id')->toArray();

        // Create facility columns
        $facilityColumns = [];
        foreach ($facilities as $facility) {
            $facilityColumns[] = TextColumn::make("facility_scores.{$facility->name}")
                ->label($facility->name)
                ->numeric()
                ->toggleable();
        }

        return $table
            ->query(
                fn () => \App\Models\BoardingHouse::query()
                    ->with('facilities', 'testimonials')
                    ->whereIn('id', $rankedIds)
                    ->orderByRaw('FIELD(id, '.implode(',', $rankedIds).')'),
            )
            ->columns(
                array_merge(
                    [
                        TextColumn::make('rank')->label('Rank')->badge()->color(
                            fn ($record) => match ((int) $record->rank) {
                                1 => 'success',
                                2 => 'warning',
                                3 => 'info',
                                default => 'gray',
                            },
                        ),
                        TextColumn::make('name')->label('Nama Kos')->searchable(),
                        TextColumn::make('price')->label('Harga')->money('IDR'),
                        TextColumn::make('distance')->label('Jarak (km)')->numeric(),
                        TextColumn::make('rating')->label('Rating')->numeric(),
                    ],
                    $facilityColumns,
                    [
                        TextColumn::make('total_facility_score')
                            ->label('Skor Fasilitas')
                            ->numeric(),
                        TextColumn::make('norm_harga')
                            ->label('Norm Harga')
                            ->numeric()
                            ->toggleable(),
                        TextColumn::make('norm_jarak')
                            ->label('Norm Jarak')
                            ->numeric()
                            ->toggleable(),
                        TextColumn::make('norm_fasilitas')
                            ->label('Norm Fasilitas')
                            ->numeric()
                            ->toggleable(),
                        TextColumn::make('norm_rating')
                            ->label('Norm Rating')
                            ->numeric()
                            ->toggleable(),
                        TextColumn::make('final_score')
                            ->label('Skor Akhir')
                            ->numeric()
                            ->badge()
                            ->color('success'),
                    ],
                ),
            )
            ->recordActions([])
            ->toolbarActions([]);
    }
}