use Carbon\Carbon;
use Inertia\Inertia;

public function boot()
{
    error_reporting(E_ALL & ~E_DEPRECATED);
    
    Carbon::useMonthsOverflow(false);
    Carbon::useStrictMode(false);
    
    if (app()->environment('local')) {
        error_reporting(E_ALL ^ E_DEPRECATED);
    }

    // Share routes with frontend
    if (class_exists('Inertia\Inertia')) {
        Inertia::share('routes', function () {
            return [
                'home' => route('home'),
                'login' => route('login'),
                // Add other essential routes
            ];
        });
    }
} 