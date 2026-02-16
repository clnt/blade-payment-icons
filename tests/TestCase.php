<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons\Tests;

use Clntdev\BladePaymentIcons\BladePaymentIconsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders(mixed $app): array //phpcs:ignore
    {
        return [
            BladePaymentIconsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp(mixed $app): void
    {
        $app['config']->set('view.paths', [
            __DIR__.'/../resources/views',
        ]);
    }
}
