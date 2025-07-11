<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;
use App\Models\Order;
use App\Models\Banner;
use App\Filament\Resources\OrderResource\Widgets\ProductPriceChart;
use Illuminate\Support\Facades\Log;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

//    protected function getFooterWidgets(): array
//    {
//        if (is_null($this->activeRelationManager)) {
//            return [
//                ProductPriceChart::class,
//            ];
//        }
//
//        return [];
//    }

    public function getTitle(): string
    {
        return $this->record->reference_number ?? 'Order Details';
    }

    protected function getHeaderMetadata(): array
    {
        $models = (new Order())->getRows();
        $banners = (new Banner())->getRows();

        $model = collect($models)->firstWhere('id', $this->record->model_id);
        $banner = null;

        if ($model && isset($model['banner_id'])) {
            $banner = collect($banners)->firstWhere('id', $model['banner_id']);
        }

        return [
            'Banner' => $banner['name'] ?? 'N/A',
            'Model' => $model['name'] ?? 'N/A',
            'Price' => $this->record->total_price ? number_format($this->record->total_price, 2) : 'N/A',
            'Location' => $this->record->location ?? 'N/A',
        ];
    }

    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record && $this->record->product_id) {
            Log::info('Setting product ID in session', [
                'productId' => $this->record->product_id,
                'record' => $this->record->toArray()
            ]);
            session(['current_product_id' => $this->record->product_id]);
        } else {
            Log::warning('No product ID found in record', [
                'record' => $this->record ? $this->record->toArray() : null
            ]);
        }
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Order Detail';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-circle-stack';
    }
}
