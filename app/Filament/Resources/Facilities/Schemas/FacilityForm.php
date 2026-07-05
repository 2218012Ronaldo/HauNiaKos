<?php

namespace App\Filament\Resources\Facilities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('icon')
                    ->columnSpan(2)
                    ->required()
                    ->disk(config('filesystems.default_public_disk'))
                    ->directory('facilities')
                    ->maxSize(1024)
                    ->acceptedFileTypes(['image/svg+xml', 'image/png',])
                    ->imagePreviewHeight('300px'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
