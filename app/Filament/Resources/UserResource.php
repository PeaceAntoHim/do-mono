<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Helpers\PermissionHelper;
use Filament\Forms\Components\Hidden;
use Rawilk\FilamentPasswordInput\Password;
use Filament\Forms\Components\Toggle;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Admin Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->maxLength(255)
                    ->required(),

                TextInput::make('email')
                    ->maxLength(255)
                    ->email()
                    ->required(),

                Password::make('password')
                    ->helperText('Minimum 8 characters')
                    ->password()
                    ->minLength(8)
                    ->maxLength(16)
                    ->newPasswordLength(16)
                    ->regeneratePassword(color: 'success')
                    ->required(fn ($context) => $context === 'create')
                    ->visible(fn ($context) => $context === 'create'),

                Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->required(),

                Toggle::make('must_change_password')
                    ->label('Use OTP (Must Change Password)')
                    ->default(false)
                    ->helperText('If enabled, user must change their password on first login')
                    ->required()
                    ->visible(fn ($context) => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('roles.name'),
                TextColumn::make('effective_permissions')
                    ->label('Effective Permissions')
                    ->getStateUsing(function (User $record) {
                        return $record->getAllPermissions()->pluck('name')->implode(', ');
                    })
                    ->wrap()
                    ->limit(50),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasRole('super-admin');
    }
}
