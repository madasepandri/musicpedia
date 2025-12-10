<?php

namespace App\Providers;

use App\Models\User; // Add this line
use Illuminate\Support\Facades\Gate; // Add this line
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', function (User $user) {
            return $user->isAdmin();
        });
    }
}
