<?php

namespace App\Filament\Resources\People\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('company')
                    ->required(),
                TextInput::make('folder_path')
                    ->required(),
            ]);
    }
}
