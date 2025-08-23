<?php

namespace App\Filament\Resources\Buckets\Pages;

use App\Filament\Resources\Buckets\BucketResource;
use App\Models\Bucket;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBuckets extends ListRecords
{
    protected static string $resource = BucketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->authorize('create', Bucket::class),
        ];
    }
}
