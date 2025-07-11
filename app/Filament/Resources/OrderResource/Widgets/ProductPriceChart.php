<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ScrapeResult;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProductPriceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Price Trends';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    
    // Set default filter to 3 months
    public ?string $filter = '3months';

    public function getProductId()
    {
        return session('current_product_id');
    }
    
    /**
     * Define the available time period filters
     */
    protected function getFilters(): ?array
    {
        return [
            '3months' => 'Last 3 Months',
            '6months' => 'Last 6 Months',
            '1year' => 'Last 1 Year',
        ];
    }

    protected function getData(): array
    {
        $productId = $this->getProductId();
        if (!$productId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Get the active filter
        $activeFilter = $this->filter;
        
        // Get payment service for price conversion
        $currencyService = app(PaymentService::class);
        
        // Calculate the date threshold based on the filter
        $dateThreshold = $this->getDateThreshold($activeFilter);

        $results = ScrapeResult::getResultsForProduct($productId);
        
        if (empty($results)) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        // Filter results based on the selected time period
        $filteredResults = collect($results)->filter(function ($result) use ($dateThreshold) {
            $createdAt = Carbon::parse($result['createdAt']);
            return $createdAt->gte($dateThreshold);
        });
        
        if ($filteredResults->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        // Convert results to collection and group by date
        $groupedResults = $filteredResults->groupBy(function ($result) {
            return Carbon::parse($result['createdAt'])->format('Y-m-d');
        })->map(function ($group) use ($currencyService) {
            $sourceCurrency = $group->first()['payment'] ?? 'USD'; // Assume all results in a group have the same payment

            $avgPrice = $group->avg('avgPrice');
            $minPrice = $group->min('minPrice');
            $maxPrice = $group->max('maxPrice');

            // Convert prices to USD first, then to active payment
            $avgPriceInUSD = $avgPrice !== null && (float) $avgPrice > 0 ? $currencyService->convertToUSD((float) $avgPrice, $sourceCurrency) : 0.0;
            $minPriceInUSD = $minPrice !== null && (float) $minPrice > 0 ? $currencyService->convertToUSD((float) $minPrice, $sourceCurrency) : 0.0;
            $maxPriceInUSD = $maxPrice !== null && (float) $maxPrice > 0 ? $currencyService->convertToUSD((float) $maxPrice, $sourceCurrency) : 0.0;

            return [
                'avgPrice' => $currencyService->convertPrice($avgPriceInUSD),
                'minPrice' => $currencyService->convertPrice($minPriceInUSD),
                'maxPrice' => $currencyService->convertPrice($maxPriceInUSD),
            ];
        })->sortKeys();

        // Get dates and prices
        $dates = $groupedResults->keys()->map(function ($date) {
            return Carbon::parse($date)->format('d M Y');
        })->toArray();

        $avgPrices = $groupedResults->pluck('avgPrice')->toArray();
        $minPrices = $groupedResults->pluck('minPrice')->toArray();
        $maxPrices = $groupedResults->pluck('maxPrice')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Average Price',
                    'data' => $avgPrices,
                    'borderColor' => '#3b82f6', // blue-500
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Minimum Price',
                    'data' => $minPrices,
                    'borderColor' => '#22c55e', // green-500
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Maximum Price',
                    'data' => $maxPrices,
                    'borderColor' => '#f97316', // orange-500
                    'backgroundColor' => 'rgba(249, 115, 22, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $dates,
        ];
    }
    
    /**
     * Get date threshold based on the selected filter
     */
    private function getDateThreshold(string $filter): Carbon
    {
        $now = Carbon::now();
        
        return match($filter) {
            '3months' => $now->subMonths(3),
            '6months' => $now->subMonths(6),
            '1year' => $now->subYear(),
            default => $now->subMonths(3), // Default to 3 months
        };
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        // Get the active payment for the chart display
        $currencyService = app(PaymentService::class);
        $activeCurrency = $currencyService->getActiveCurrency();
        $currencySymbol = $activeCurrency->code;
        
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => "function(value) { return '{$currencySymbol} ' + value.toLocaleString(); }"
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }
}