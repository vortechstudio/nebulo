<?php

namespace App\Filament\Resources\Objects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ObjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) ($column->getState() ?? '');
                        return mb_strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('path')
                    ->label('Chemin')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) ($column->getState() ?? '');
                        return mb_strlen($state) > 40 ? $state : null;
                    }),

                TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match (explode('/', $state)[0]) {
                        'image' => 'success',
                        'video' => 'info',
                        'audio' => 'warning',
                        'text' => 'gray',
                        'application' => 'primary',
                        default => 'secondary',
                    })
                    ->searchable(),

                TextColumn::make('size')
                    ->label('Taille')
                    ->formatStateUsing(function (string $state): string {
                        $bytes = (int) $state;
                        if ($bytes >= 1073741824) {
                            return number_format($bytes / 1073741824, 2) . ' GB';
                        } elseif ($bytes >= 1048576) {
                            return number_format($bytes / 1048576, 2) . ' MB';
                        } elseif ($bytes >= 1024) {
                            return number_format($bytes / 1024, 2) . ' KB';
                        }
                        return $bytes . ' B';
                    })
                    ->sortable(),

                TextColumn::make('bucket.name')
                    ->label('Bucket')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bucket')
                    ->label('Bucket')
                    ->relationship('bucket', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('mime_type')
                    ->label('Type de fichier')
                    ->options([
                        'image' => 'Images',
                        'video' => 'Vidéos',
                        'audio' => 'Audio',
                        'text' => 'Texte',
                        'application' => 'Applications',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->where('mime_type', 'like', $value . '/%')
                        );
                    }),

                Filter::make('large_files')
                    ->label('Fichiers volumineux (> 10MB)')
                    ->query(function (Builder $query) {
                        $TEN_MB = 10 * 1024 * 1024;
                        return $query->where('size', '>', $TEN_MB);
                    }),

                Filter::make('recent')
                    ->label('Récents (7 derniers jours)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir')
                    ->authorize('view'),

                EditAction::make()
                    ->label('Modifier')
                    ->authorize('update'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer sélectionnés'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
