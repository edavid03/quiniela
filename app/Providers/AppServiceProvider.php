<?php

namespace App\Providers;

use App\Services\Football\FootballDataOrgClient;
use App\Services\Football\FootballDataProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Swappear el proveedor de resultados (p. ej. a API-Football si el tier
        // free de football-data.org no cubriera el Mundial) es cambiar esta linea.
        $this->app->bind(FootballDataProvider::class, FootballDataOrgClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
