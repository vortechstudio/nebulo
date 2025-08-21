<?php

namespace App\Filament\Resources\Buckets;

use App\Filament\Resources\Buckets\Pages\CreateBucket;
use App\Filament\Resources\Buckets\Pages\EditBucket;
use App\Filament\Resources\Buckets\Pages\ListBuckets;
use App\Filament\Resources\Buckets\Schemas\BucketForm;
use App\Filament\Resources\Buckets\Tables\BucketsTable;
use App\Models\Bucket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BucketResource extends Resource
{
    protected static ?string $model = Bucket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Buckets';

    public static function form(Schema $schema): Schema
    {
        return BucketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BucketsTable::configure($table);
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
            'index' => ListBuckets::route('/'),
            'create' => CreateBucket::route('/create'),
            'edit' => EditBucket::route('/{record}/edit'),
        ];
    }
}
