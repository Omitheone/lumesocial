<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate routes for the frontend';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $routes = [
            'home' => route('home'),
            'login' => route('login'),
            // Add other routes
        ];
        
        file_put_contents(
            resource_path('js/routes.json'),
            json_encode($routes, JSON_PRETTY_PRINT)
        );
    }
} 