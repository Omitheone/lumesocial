<?php

namespace LumeSocial\Providers;

use Illuminate\Support\ServiceProvider;
use LumeSocial\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConsoleKernelContract::class, ConsoleKernel::class);
        
        // ... other bindings ...
    }
} 