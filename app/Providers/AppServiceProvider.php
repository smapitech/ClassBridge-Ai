<?php

namespace App\Providers;

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
        // Local browser requests can inherit stale MySQL env values from the
        // long-running PHP process. When the app is being served from localhost,
        // force the app onto the checked-in SQLite database so login and the UI
        // use the same data source as the CLI.
        if (app()->runningInConsole()) {
            return;
        }

        $host = request()->getHost();

        if (! in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            return;
        }

        $sqlitePath = database_path('database.sqlite');

        if (! is_file($sqlitePath)) {
            return;
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $sqlitePath,
            'session.driver' => 'file',
            'cache.default' => 'file',
            'queue.default' => 'sync',
            'broadcasting.default' => 'log',
        ]);
    }
}
