<?php

namespace NowakAdmin\SelectableKeyValue;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event) {
            Nova::mix('selectable-key-value', __DIR__.'/../dist/mix-manifest.json');
        });

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
