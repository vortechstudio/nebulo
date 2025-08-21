<?php

namespace App\Filament\Resources\Objects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ObjectsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('path')
                    ->required(),
                TextInput::make('mime_type')
                    ->required(),
                TextInput::make('size')
                    ->required(),
                TextInput::make('metadata'),
                Select::make('bucket_id')
                    ->relationship('bucket', 'name')
                    ->required(),
            ]);
    }
}
