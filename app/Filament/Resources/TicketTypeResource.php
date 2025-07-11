<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketTypeResource\Pages;
use App\Models\TicketType;
use App\Models\Event;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Support\RawJs;


class TicketTypeResource extends Resource
{
    protected static ?string $model = TicketType::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Tickets';
    protected static ?string $navigationLabel = 'Ticket Types';
    protected static ?int $navigationSort = 4;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('event_id')
                ->label('Event')
                ->options(Event::query()->pluck('title', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->label('Ticket Name')
                ->required()
                ->maxLength(100),
            Forms\Components\TextInput::make('price')
            ->label('Price')
            ->mask(RawJs::make('$money($input)'))
            ->stripCharacters(',')
            ->numeric()
            ->required(),
            Forms\Components\TextInput::make('stock')
                ->label('Stock')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')->label('Event')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Ticket Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Price')->formatStateUsing(fn ($state) => number_format($state, 0, '.', ',')),
                Tables\Columns\TextColumn::make('stock')->label('Stock'),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->tooltip('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketTypes::route('/'),
            'create' => Pages\CreateTicketType::route('/create'),
            'edit' => Pages\EditTicketType::route('/{record}/edit'),
        ];
    }
}
