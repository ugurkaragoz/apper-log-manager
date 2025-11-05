<?php

namespace Apper\LogManager;

use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Config dosyasını merge et
        $this->mergeConfigFrom(
            __DIR__ . '/../config/log-manager.php',
            'log-manager'
        );

        // LogService'i singleton olarak kaydet
        $this->app->singleton(LogService::class, function ($app) {
            return new LogService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Config dosyasını yayınla
        $this->publishes([
            __DIR__ . '/../config/log-manager.php' => config_path('log-manager.php'),
        ], 'log-manager-config');
    }
}