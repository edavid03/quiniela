<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        // Grabamos TODO (no solo errores): el objetivo es observar las peticiones
        // reales del sistema, no solo los fallos. El crecimiento de las tablas se
        // controla con la poda programada (telescope:prune en routes/console.php).
        Telescope::filter(fn (IncomingEntry $entry) => true);
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters([
            '_token',
            'password',
            'password_confirmation',
        ]);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (User $user) {
            // Telescope es GLOBAL: expone peticiones/queries/payloads de TODAS las
            // ligas. Solo el superadmin (sin tenant) puede verlo; dárselo a un
            // liga_admin seria una fuga de datos cross-tenant.
            return $user->isSuperAdmin();
        });
    }
}
