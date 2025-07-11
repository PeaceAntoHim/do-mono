<?php

namespace App\Filament\Resources\TicketTypeResource\Pages;

use App\Filament\Resources\TicketTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketType extends CreateRecord
{
    protected static string $resource = TicketTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $basePrice = $data['price'];

        // Clean formula: apply margin (15%) then admin fee (2.5%)
        $margin = 0.15;
        $adminFee = 0.025;

        $withMargin = $basePrice * (1 + $margin);
        $finalPrice = $withMargin * (1 + $adminFee);

        $data['price'] = round($finalPrice, 2); // round to 2 decimal places

        return $data;
    }
}
