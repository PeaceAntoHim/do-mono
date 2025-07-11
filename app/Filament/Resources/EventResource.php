<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\Venue;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Tickets';
    protected static ?string $navigationLabel = 'Events';
    protected static ?int $navigationSort = 2;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Event Title')
                                ->required()
                                ->maxLength(100),

                            Forms\Components\TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(100),

                            Forms\Components\DateTimePicker::make('date')
                                ->label('Date & Time (WIB)')
                                ->timezone('Asia/Jakarta')
                                ->seconds(false)
                                ->required(),

                            Forms\Components\Textarea::make('description')
                                ->label('Description')
                                ->columnSpanFull(),

                            Forms\Components\Select::make('venue_id')
                                ->label('Venue')
                                ->options(Venue::query()->pluck('name', 'id'))
                                ->required()
                                ->searchable(),

                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->required()
                                ->options([
                                    'draft' => 'Draft',
                                    'published' => 'Published',
                                    'cancelled' => 'Cancelled',
                                    'postponed' => 'Postponed',
                                    'archived' => 'Archived',
                                ])
                                ->native(false)
                                ->searchable(),

                            Forms\Components\TextInput::make('thumbnail_url')
                                ->label('Thumbnail URL')
                                ->url()
                                ->required(),
                            Forms\Components\Toggle::make('is_private')->label('Private Event')->helperText('If enabled, this event will be accessible only via a private link.')->default(false),
                        ]),
                ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('venue.name')
                    ->label('Venue')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->label('Date & Time (WIB)')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('venue_id')
                    ->label('Venue')
                    ->relationship('venue', 'name')
                    ->searchable()
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvent::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
