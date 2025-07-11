<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentView;
use Filament\Panel;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the "page-loader" component to be rendered on every page
        FilamentView::registerRenderHook(
            'panels::body.start',
            fn (): string => view('components.filament.page-loader')->render(),
        );
        
        // Add assets for page transition
        FilamentAsset::register([
            Js::make('navigation-loader', __DIR__ . '/../../resources/js/navigation-loader.js'),
        ]);
    }
} 