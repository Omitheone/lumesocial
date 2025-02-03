<?php

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use LumeSocial\Providers\AppServiceProvider;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class TestCase extends OrchestraTestCase
{
    use MockeryPHPUnitIntegration;

    protected function getPackageProviders($app): array
    {
        return [
            AppServiceProvider::class,
            \LumeSocial\Mixpost\MixpostServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
