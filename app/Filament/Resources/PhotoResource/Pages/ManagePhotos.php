<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePhotos extends ManageRecords
{
    protected static string $resource = PhotoResource::class;

    protected function getActions(): array
    {
        return [
            // Nonaktifkan create action
            // Actions\CreateAction::make(),
        ];
    }
}