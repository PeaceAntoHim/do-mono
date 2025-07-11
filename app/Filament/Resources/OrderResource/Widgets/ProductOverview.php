<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource\Pages\ListOrder;
use App\Models\Order;
use App\Models\ScrapeResult;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class ProductOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListOrder::class;
    }

    protected function getStats(): array
    {
        // Get the current query with filters
        $query = $this->getPageTableQuery();

        $orderProducts = $query->get();

        $validProducts = $orderProducts->filter(function ($product) {
            return $product->avg_price !== null && (float) $product->avg_price > 0;
        });

        $totalPriceInUSD = $validProducts->sum(function ($product) {
            $sourceCurrency = $product->payment ?? 'USD';
            return 0;
        });

        $averagePriceInUSD = $validProducts->count() > 0 ? $totalPriceInUSD / $validProducts->count() : 0;
        $formattedAverage = $averagePriceInUSD > 0 ? $averagePriceInUSD : 'N/A';

        $priceChangeData = $this->getPriceChangeData($orderProducts);

        $productIds = $orderProducts->pluck('product_id');
        $filteredOrder = 0;

        return [
                Stat::make('Total Orders', $orderProducts->count()),
                Stat::make('Average Price', $formattedAverage)
                    ->description($priceChangeData['description'])
                    ->descriptionIcon($priceChangeData['icon'])
                    ->descriptionColor($priceChangeData['color'])
                    ->chart($priceChangeData['chart']),
                Stat::make('Total Scrape Logs', $filteredOrder->count()),
            ];
    }

    protected function getPriceChangeData(Collection $orders): array
    {
        // Calculate price changes for each product
        $productChanges = collect();
        foreach ($orders as $product) {
            $results = ScrapeResult::getResultsForProduct($product->product_id);
            if (empty($results)) continue;

            // Group results by timestamp and sort descending
            $groupedResults = collect($results)->groupBy(function ($result) {
                return Carbon::parse($result['createdAt'])->format('Y-m-d H:i:s');
            })->sortKeysDesc();

            if ($groupedResults->count() < 2) continue;

            // Get latest and previous prices for this product
            $timestamps = $groupedResults->keys()->take(2);
            $latestPrice = $groupedResults[$timestamps[0]]->avg('avgPrice');
            $previousPrice = $groupedResults[$timestamps[1]]->avg('avgPrice');

            // Calculate percentage change for this product
            $change = $latestPrice - $previousPrice;
            $percentageChange = ($change / $previousPrice) * 100;

            $productChanges->push([
                'product_id' => $product->product_id,
                'latest_price' => $latestPrice,
                'previous_price' => $previousPrice,
                'change' => $change,
                'percentage_change' => $percentageChange
            ]);
        }

        if ($productChanges->isEmpty()) {
            return [
                'description' => 'No price change data available',
                'icon' => 'heroicon-m-minus-circle',
                'color' => 'gray',
                'chart' => [],
            ];
        }

        // Calculate average percentage change across all orders
        $averagePercentageChange = $productChanges->avg('percentage_change');
        $isPositive = $averagePercentageChange > 0;
        $icon = $isPositive ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $color = $isPositive ? 'success' : 'danger';

        // Prepare chart data (average prices across all orders)
        $chartData = [
            $productChanges->avg('previous_price'),
            $productChanges->avg('latest_price')
        ];

        return [
            'description' => sprintf(
                '%s%.2f%% from previous period',
                $isPositive ? '+' : '',
                $averagePercentageChange
            ),
            'icon' => $icon,
            'color' => $color,
            'chart' => $chartData,
        ];
    }
}
