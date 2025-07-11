<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\ScrapingFrequency;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['product_id'] = 'product_' . Str::random(20);
        return $data;
    }

    protected function afterCreate(): void
    {
        // Get the frequency value from the form
        $frequency = $this->data['frequency'] ?? 3;
        
        // Get the brand_id from the created product's model
        $brandId = $this->record->model->brand_id;
        
        // Set the frequency via API
        $result = ScrapingFrequency::setFrequency(
            $brandId,
            $this->record->product_id,
            $frequency
        );

        if (!$result) {
            Notification::make()
                ->title('Warning')
                ->body('Order created but failed to set scraping frequency. Default frequency (3 days) will be used.')
                ->warning()
                ->persistent()
                ->duration(5000)
                ->send();
        }
    }
}