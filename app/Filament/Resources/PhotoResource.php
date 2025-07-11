<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Models\Photo;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use App\Helpers\PermissionHelper;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

        // Add these lines for navigation grouping
        protected static ?string $navigationGroup = 'Photo Manager';
    
        protected static ?int $navigationSort = 5;
        
        protected static ?string $navigationLabel = 'Search Product';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SKU Input
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->placeholder('Enter SKU'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\SearchProduct::route('/'),
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