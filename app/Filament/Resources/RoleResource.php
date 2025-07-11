<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Helpers\PermissionHelper;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    // Add these lines for navigation grouping
    protected static ?string $navigationGroup = 'User Management';
    
    protected static ?int $navigationSort = 8;
    
    protected static ?string $navigationLabel = 'Roles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->label('Role Name')
                ->required(),

                CheckboxList::make('permissions')
                ->relationship('permissions', 'name')
                ->searchable()
                ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable()
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return PermissionHelper::can(static::class, 'read');
    }

    public static function canCreate(): bool
    {
        return PermissionHelper::can(static::class, 'create');
    }

    public static function canEdit($record): bool
    {
        return PermissionHelper::can(static::class, 'edit');
    }

    public static function canDelete($record): bool
    {
        return PermissionHelper::can(static::class, 'delete');
    }

    public static function canDeleteAny(): bool
    {
        return PermissionHelper::can(static::class, 'delete');
    }
}
