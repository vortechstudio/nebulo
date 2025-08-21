<?php

namespace App\Filament\Resources\Objects;

use App\Filament\Resources\Objects\Pages\CreateObjects;
use App\Filament\Resources\Objects\Pages\EditObjects;
use App\Filament\Resources\Objects\Pages\ListObjects;
use App\Filament\Resources\Objects\Schemas\ObjectsForm;
use App\Filament\Resources\Objects\Tables\ObjectsTable;
use App\Models\Objects;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ObjectsResource extends Resource
{
    protected static ?string $model = Objects::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Objets';

    public static function form(Schema $schema): Schema
    {
        return ObjectsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObjects::route('/'),
            'create' => CreateObjects::route('/create'),
            'edit' => EditObjects::route('/{record}/edit'),
        ];
    }
}
