<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons;

use Clntdev\BladePaymentIcons\Components\PaymentIcon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladePaymentIconsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/blade-payment-icons.php',
            'blade-payment-icons'
        );

        $this->app->singleton(CardMetadata::class, function (): CardMetadata {
            return new CardMetadata;
        });

        $this->app->singleton(CardUtilities::class, function (Application $app): CardUtilities {
            return new CardUtilities($app->make(CardMetadata::class));
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'blade-payment-icons');

        Blade::component('payment-icon', PaymentIcon::class);

        if ($this->app->runningInConsole() === false) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/blade-payment-icons.php' => config_path('blade-payment-icons.php'),
        ], 'blade-payment-icons-config');

        $this->publishes([
            __DIR__.'/../resources/svg' => resource_path('vendor/blade-payment-icons/svg'),
        ], 'blade-payment-icons-svg');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/blade-payment-icons'),
        ], 'blade-payment-icons-views');
    }
}
