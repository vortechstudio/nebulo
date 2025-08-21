<?php

namespace App\Filament\Resources\Objects\Pages;

use App\Filament\Resources\Objects\ObjectsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditObjects extends EditRecord
{
    protected static string $resource = ObjectsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
