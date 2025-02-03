<?php

namespace LumeSocial\Providers;

use Illuminate\Support\ServiceProvider;
use LumeSocial\Contracts\ContentGeneratorInterface;
use LumeSocial\Services\AI\ContentGenerator;
use LumeSocial\Models\AiSettings;

class MixpostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentGeneratorInterface::class, function ($app) {
            return new ContentGenerator(
                config('services.openai.api_key'),
                AiSettings::first()
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/mixpost.php' => config_path('mixpost.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mixpost'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/mixpost'),
        ], 'public');
    }

    public function provides(): array
    {
        return [
            ContentGeneratorInterface::class,
        ];
    }
} 