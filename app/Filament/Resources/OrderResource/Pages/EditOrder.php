<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Edit ' . ($this->record->reference_number ?? '');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->color('danger')
        ];
    }

    public function getContentTabLabel(): ?string
    {
        return 'Order Detail';
    }
    
    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-circle-stack';
    }
    
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}