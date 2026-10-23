<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // SQLite otherwise allows orphan references on old Laravel versions.
        // Do not open a nonexistent file while running first-time key setup.
        $file = config('database.connections.sqlite.database');
        if (config('database.default') === 'sqlite' && ($file === ':memory:' || is_file($file))) {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
