<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Order;
use App\Models\User;

class SourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    protected static ?string $label = 'Order';

    protected static ?string $modelLabel = 'Order';

    protected static ?string $icon = 'heroicon-o-shopping-cart';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uuid')
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Order UUID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->headerActions([
                // Optional: create new orders if relevant to user
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query();
    }
}
