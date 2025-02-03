<?php

namespace LumeSocial\Providers;

use Illuminate\Support\ServiceProvider;
use LumeSocial\Services\AI\ContentGenerator;
use LumeSocial\Services\AI\ContentReviewer;
use LumeSocial\Services\AI\ImageSelector;
use LumeSocial\Models\AiSettings;
use OpenAI\Client;

class LumeSocialServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            $settings = $app->make(AiSettings::class);
            return new Client($settings->api_key);
        });

        $this->app->singleton(ContentGenerator::class);
        $this->app->singleton(ContentReviewer::class);
        $this->app->singleton(ImageSelector::class);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
} 