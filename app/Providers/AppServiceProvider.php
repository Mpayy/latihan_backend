<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::before(function ($user, $ability) {
            // Mengizinkan admin melakukan apa saja
            if (isset($user->is_admin) && $user->is_admin) {
                return true;
            }
            return null; // Lanjut ke Policy reguler
        });
    }
}
