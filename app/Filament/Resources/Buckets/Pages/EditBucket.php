<?php

namespace App\Filament\Resources\Buckets\Pages;

use App\Filament\Resources\Buckets\BucketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBucket extends EditRecord
{
    protected static string $resource = BucketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
