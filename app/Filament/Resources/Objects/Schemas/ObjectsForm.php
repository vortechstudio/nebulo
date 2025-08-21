<?php

namespace App\Filament\Resources\Objects\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class ObjectsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom de l\'objet')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Nom unique pour identifier l\'objet'),

                FileUpload::make('file')
                    ->label('Fichier à télécharger')
                    ->disk('objectstorage')
                    ->directory(fn ($get) => $get('bucket.name') ?? 'temp')
                    ->acceptedFileTypes(['*'])
                    ->maxSize(1024 * 1024) // 1GB
                    ->helperText('Sélectionnez le fichier à stocker dans le bucket')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('mime_type', $state->getMimeType());
                            $set('size', $state->getSize());
                            if (!$set('name')) {
                                $set('name', $state->getClientOriginalName());
                            }
                        }
                    }),

                TextInput::make('path')
                    ->label('Chemin de stockage')
                    ->required()
                    ->maxLength(500)
                    ->helperText('Chemin relatif où l\'objet sera stocké'),

                TextInput::make('mime_type')
                    ->label('Type MIME')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Type de contenu du fichier (ex: image/jpeg, text/plain)'),

                TextInput::make('size')
                    ->label('Taille')
                    ->numeric()
                    ->suffix('bytes')
                    ->required()
                    ->helperText('Taille du fichier en octets'),

                Textarea::make('metadata')
                    ->label('Métadonnées')
                    ->rows(3)
                    ->maxLength(1000)
                    ->helperText('Métadonnées JSON additionnelles pour l\'objet')
                    ->placeholder('{"description": "Mon fichier", "tags": ["important"]}'),

                Select::make('bucket_id')
                    ->label('Bucket')
                    ->relationship('bucket', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Sélectionnez le bucket de destination'),
            ]);
    }
}
