<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons;

use BladeUI\Icons\Factory;
use Clntdev\BladePaymentIcons\Components\PaymentIcon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
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

        $this->registerBladeIcons();
    }

    protected function registerBladeIcons(): void
    {
        $this->callAfterResolving(Factory::class, function (Factory $factory): void {
            $config = config('blade-payment-icons');

            if (Arr::get($config, 'blade_icons.enabled', true) === false) {
                return;
            }

            $this->registerDefaultIconSet($factory, $config);
            $this->registerFormatIconSets($factory, $config);
        });
    }

    /**
     * @param array<string, mixed> $config
     * @throws \BladeUI\Icons\Exceptions\CannotRegisterIconSet
     */
    protected function registerDefaultIconSet(Factory $factory, array $config): void
    {
        $configFormat = Arr::get($config, 'default_format', Format::Flat);
        $defaultFormat = $configFormat instanceof Format ? $configFormat : (
            Format::tryFrom($configFormat) ?? Format::Flat
        );

        $factory->add('payment-icons', [
            'path' => __DIR__ . '/../resources/svg/' . $defaultFormat->directory(),
            'prefix' => Arr::get($config, 'prefix', 'payicon'),
            'fallback' => Arr::get($config, 'fallback', Format::FALLBACK_ICON),
        ]);
    }

    /**
     * @param array<string, mixed> $config
     * @throws \BladeUI\Icons\Exceptions\CannotRegisterIconSet
     */
    protected function registerFormatIconSets(Factory $factory, array $config): void
    {
        $fallback = Arr::get($config, 'fallback', Format::FALLBACK_ICON);

        foreach (Arr::get($config, 'blade_icons.sets', []) as $format => $setConfig) {
            $factory->add('payment-icons-' . $format, [
                'path' => __DIR__ . '/../resources/svg/' . $format,
                'prefix' => Arr::get($setConfig, 'prefix'),
                'fallback' => $fallback,
            ]);
        }
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
