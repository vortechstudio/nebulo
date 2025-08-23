<?php

namespace App\Filament\Resources\Buckets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BucketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(1000)
                    ->rows(3),
                TextInput::make('size')
                    ->numeric()
                    ->minValue(0)
                    ->rule('integer')
                    ->suffix('bytes')
                    ->helperText('Maximum storage size for this bucket'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }
}
