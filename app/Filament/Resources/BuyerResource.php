<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuyerResource\Pages;
use App\Models\Buyer;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms;


class BuyerResource extends Resource
{
    protected static ?string $model = Buyer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->maxLength(255)
                                ->required(),

                            TextInput::make('email')
                                ->maxLength(255)
                                ->email()
                                ->required(),
                            TextInput::make('phone_number')
                                ->maxLength(255)
                                ->required(),
                            Forms\Components\Toggle::make('is_member')->label('Member Dariordal')->helperText('If enabled, this would be set as member.')->default(false),
                        ]),
                    ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('phone_number'),
                TextColumn::make('email_verified')->dateTime()->label('Email Verified At'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Replace individual actions with a dropdown
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuyers::route('/'),
            'create' => Pages\CreateBuyer::route('/create'),
            'edit' => Pages\EditBuyer::route('/{record}/edit'),
        ];
    }
}
