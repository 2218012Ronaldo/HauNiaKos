<?php

namespace App\Filament\Resources\CriteriaWeights\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CriteriaWeightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('weight')->disabled()
            ]);
    }
}
