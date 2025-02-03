<?php

namespace LumeSocial\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI\Client;
use OpenAI\Factory;
use LumeSocial\Services\AI\ContentGenerator;
use LumeSocial\Services\AI\ContentReviewer;
use LumeSocial\Services\AI\ImageSelector;
use LumeSocial\Models\AiSettings;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return (new Factory())->withApiKey(config('services.openai.api_key'))
                                ->withOrganization(config('services.openai.org_id'))
                                ->make();
        });

        $this->app->singleton(ContentGenerator::class);
        $this->app->singleton(ContentReviewer::class);
        $this->app->singleton(ImageSelector::class);
    }

    public function boot(): void
    {
        // Register any necessary routes, configs, or other boot-time setup
    }
} 