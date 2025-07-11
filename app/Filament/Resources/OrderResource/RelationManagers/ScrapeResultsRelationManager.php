<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ScrapeResult;
use App\Models\Source;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Services\PaymentService;

class ScrapeResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'sources';
    
    protected static ?string $title = 'Scrape Results';
    
    protected static ?string $label = 'Scrape Result';
    
    protected static ?string $modelLabel = 'Scrape Result';

    protected static ?string $icon = 'heroicon-o-bars-3';

    public function form(Form $form): Form
    {
        // This is required by the RelationManager, but we don't use it
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        $productId = $this->getOwnerRecord()->product_id ?? null;
        $results = ScrapeResult::getResultsForProduct($productId);
        
        // Calculate price statistics
        $currencyService = app(PaymentService::class);

        $validResults = collect($results)->filter(function ($result) {
            return !empty($result['maxPrice']) && (float) $result['maxPrice'] > 0;
        });

        $avgPriceInUSD = $validResults->sum(function ($result) use ($currencyService) {
            $sourceCurrency = $result['payment'] ?? 'USD';
            return $currencyService->convertToUSD((float) $result['maxPrice'], $sourceCurrency);
        }) / ($validResults->count() > 0 ? $validResults->count() : 1);

        $minPriceInUSD = $validResults->min(function ($result) use ($currencyService) {
            $sourceCurrency = $result['payment'] ?? 'USD';
            return $currencyService->convertToUSD((float) $result['maxPrice'], $sourceCurrency);
        });

        $maxPriceInUSD = $validResults->max(function ($result) use ($currencyService) {
            $sourceCurrency = $result['payment'] ?? 'USD';
            return $currencyService->convertToUSD((float) $result['maxPrice'], $sourceCurrency);
        });

        $formattedAvg = $avgPriceInUSD > 0 ? $currencyService->formatPrice($avgPriceInUSD) : 'N/A';
        $formattedMin = $minPriceInUSD > 0 ? $currencyService->formatPrice($minPriceInUSD) : 'N/A';
        $formattedMax = $maxPriceInUSD > 0 ? $currencyService->formatPrice($maxPriceInUSD) : 'N/A';
        
        return $table
            ->heading('Scrape Results')
            ->description('Results from scraping sources')
            ->header(
                view('filament.resources.product-resource.scrape-results-price-stats', [
                    'avgPrice' => $formattedAvg,
                    'minPrice' => $formattedMin,
                    'maxPrice' => $formattedMax
                ])
            )
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('createdAt')
                    ->label('Scrape Date')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('yearOfProduction')
                    ->label('Year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gradingLabel')
                    ->label('Condition')
                    ->limit(30)
                    ->sortable()
                    ->tooltip(fn ($record) => $record->gradingLabel ?? ''),
                Tables\Columns\TextColumn::make('conditionValue')
                    ->label('Condition Value')
                    ->formatStateUsing(function ($record) {
                        if (!$record->gradingLabel) return '-';
                        return str_contains($record->gradingLabel, '100%') ? 'New' : 'Pre-Owned';
                    }),
                Tables\Columns\TextColumn::make('scopeOfDelivery')
                    ->label('Completeness')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->scopeOfDelivery ?? ''),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->location ?? ''),
                Tables\Columns\TextColumn::make('maxPrice')
                    ->label('Price')
                    ->formatStateUsing(function ($state, $record) use ($currencyService) {
                        if (!$state) return '-';
                        $sourceCurrency = $record->payment ?? 'USD';
                        $priceInUSD = $currencyService->convertToUSD((float) $state, $sourceCurrency);
                        return $currencyService->formatPrice($priceInUSD);
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options(function () {
                        $productId = $this->getOwnerRecord()->product_id;
                        $results = ScrapeResult::getResultsForProduct($productId);
                        $sources = collect($results)->pluck('source')->unique()->filter()->mapWithKeys(function ($source) {
                            return [$source => ucfirst($source)];
                        })->toArray();
                        
                        return $sources;
                    }),
                Tables\Filters\SelectFilter::make('yearOfProduction')
                    ->label('Year of Production')
                    ->multiple()
                    ->options(function () {
                        $productId = $this->getOwnerRecord()->product_id;
                        $results = ScrapeResult::getResultsForProduct($productId);
                        return collect($results)
                            ->pluck('yearOfProduction')
                            ->unique()
                            ->filter()
                            ->sort()
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('conditionValue')
                    ->label('Condition')
                    ->multiple()
                    ->options([
                        'New' => 'New',
                        'Pre-Owned' => 'Pre-Owned',
                    ]),
                Tables\Filters\SelectFilter::make('time_period')
                    ->label('Time Period')
                    ->options([
                        '7d' => 'Last 7 Days',
                        '30d' => 'Last 30 Days',
                        '90d' => 'Last 90 Days',
                        '1y' => 'Last Year',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $date = now();
                        switch ($data['value']) {
                            case '7d':
                                $date = $date->subDays(7);
                                break;
                            case '30d':
                                $date = $date->subDays(30);
                                break;
                            case '90d':
                                $date = $date->subDays(90);
                                break;
                            case '1y':
                                $date = $date->subYear();
                                break;
                        }

                        return $query->where('createdAt', '>=', $date);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('visit')
                    ->label('Visit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn ($record) => $record->url ? ('https://' . str_replace(['https://', 'http://'], '', $record->url)) : '#')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No scrape results yet')
            ->emptyStateDescription('Scrape results will appear here after scraping is completed.')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass')
            ->defaultSort('createdAt', 'desc')
            ->poll('10s');
    }
    
    // Override the getEloquentQuery method to use Sushi's query builder
    protected function getTableQuery(): Builder
    {
        $productId = $this->getOwnerRecord()->product_id ?? null;
        
        if (!$productId) {
            return ScrapeResult::query()->where('id', 0);
        }
        
        // Get fresh results from API and store them in session for Sushi
        session(['current_product_id' => $productId]);
        
        // Actually fetch the data and force Sushi to rebuild its schema
        $results = ScrapeResult::getResultsForProduct($productId);
        
        // If we have results, ensure Sushi has initialized its schema
        if (!empty($results)) {
            // This forces Sushi to rebuild its schema with the first result
            ScrapeResult::sushiShouldCache();
            
            // Use syncResultsForProduct to ensure data is loaded into Sushi
            ScrapeResult::syncResultsForProduct($productId);
        }
        
        // Then query as normal
        return ScrapeResult::query()->where('productId', $productId);
    }
}