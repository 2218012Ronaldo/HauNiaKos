<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TestimonialsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('photo')
                ->required()
                ->image()
                ->disk(config('filesystems.default_public_disk'))
                ->directory('testimonials')
                ->imagePreviewHeight('300px')
                ->columnSpan(2),
            Select::make('boarding_house_id')
                ->relationship('boardingHouse', 'name')
                ->columnSpan(2)
                ->required(),
            TextInput::make('name')->required()->columnSpan(2),

            Textarea::make('content')->required()->columnSpanFull(),
            TextInput::make('rating')->required()->numeric()->columnSpan(2),
        ]);
    }
}
