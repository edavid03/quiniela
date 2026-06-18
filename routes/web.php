<?php

use App\Http\Controllers\AdminPartidoResultadoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LigaController;
use App\Http\Controllers\LigaUserController;
use App\Http\Controllers\MiDesempenoController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PronosticoController;
use App\Http\Controllers\PronosticoPublicoController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Models\Equipo;
use App\Models\Liga;
use App\Models\Partido;
use App\Models\Prediccion;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    // Invitado: landing publica de producto. Autenticado: directo a su area.
    if ($user === null) {
        return view('landing');
    }

    if ($user->isSuperAdmin()) {
        return redirect()->route('superadmin.ligas.index');
    }

    return $user->liga
        ? redirect()->route('liga.dashboard', ['liga' => $user->liga->slug])
        : view('landing');
});

Route::post('/contacto', [ContactController::class, 'send'])
    ->middleware('throttle:5,1')
    ->name('contacto.send');

// /superadmin a secas (sin /login) -> a su login o a ligas si ya hay sesion.
Route::get('/superadmin', function () {
    return auth()->user()?->isSuperAdmin()
        ? redirect()->route('superadmin.ligas.index')
        : redirect()->route('superadmin.login');
});

/*
|--------------------------------------------------------------------------
| Superadmin (sin tenant) — DEFINIR PRIMERO para que sus rutas fijas no
| sean capturadas por el grupo de tenant (slug en la raiz).
|--------------------------------------------------------------------------
*/
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [SuperAdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [SuperAdminAuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    Route::middleware(['auth', 'superadmin', 'no.cache'])->group(function () {
        Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');

        Route::resource('ligas', LigaController::class)->except(['show']);

        Route::get('resultados', [AdminPartidoResultadoController::class, 'edit'])->name('resultados.edit');
        Route::get('resultados/sync-status', [AdminPartidoResultadoController::class, 'syncStatus'])->name('resultados.sync-status');
        Route::post('resultados', [AdminPartidoResultadoController::class, 'update'])->name('resultados.update');
    });
});

/*
|--------------------------------------------------------------------------
| Tenant — el slug de la liga vive en la raiz: /{slug}/...
| DEFINIR DESPUES de las rutas fijas.
|--------------------------------------------------------------------------
*/
Route::prefix('{liga:slug}')->middleware('liga')->name('liga.')->group(function () {
    // /{slug} a secas: a su dashboard si hay sesion en esta liga, si no al login.
    Route::get('/', function (Liga $liga) {
        $user = auth()->user();

        return $user && $user->liga_id === $liga->id
            ? redirect()->route('liga.dashboard', ['liga' => $liga])
            : redirect()->route('liga.login', ['liga' => $liga]);
    })->name('home');

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.store');

        Route::get('olvide-clave', [PasswordResetController::class, 'create'])->name('password.request');
        Route::post('olvide-clave', [PasswordResetController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('password.email');
        Route::get('recuperar-clave', [PasswordResetController::class, 'edit'])->name('password.reset');
        Route::post('recuperar-clave', [PasswordResetController::class, 'update'])->name('password.update');

        Route::get('invitacion/{token}', [InvitationController::class, 'show'])->name('invitation.show');
        Route::post('invitacion/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
    });

    Route::middleware(['auth', 'no.cache'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', function () {
            $user = auth()->user();

            // El proximo partido con pronosticos abiertos: alimenta el contador y
            // el detalle (equipos/fecha) que se muestra en la card de cierre.
            $proximoPartido = Partido::query()
                ->conPronosticosAbiertos()
                ->with(['local', 'visitante'])
                ->orderBy('fecha_utc')
                ->first();

            return view('dashboard', [
                'teamCount' => Equipo::query()->count(),
                'matchCount' => Partido::query()->count(),
                'predictionCount' => Prediccion::query()
                    ->where('usuario_id', $user->id)
                    ->count(),
                'playerCount' => User::query()
                    ->where('role', User::ROLE_LIGA_USER)
                    ->count(),
                'predictionDeadline' => $proximoPartido?->fechaCierrePronosticosUtc(),
                'proximoPartido' => $proximoPartido,
                'nextMatches' => Partido::query()
                    ->with(['local', 'visitante'])
                    ->orderBy('fecha_utc')
                    ->take(5)
                    ->get(),
            ]);
        })->name('dashboard');

        Route::get('rankings', [RankingController::class, 'index'])->name('rankings.index');
        Route::get('resultados', [ResultadoController::class, 'index'])->name('resultados.index');
        Route::get('pronosticos-publicos', [PronosticoPublicoController::class, 'index'])->name('pronosticos-publicos.index');
        Route::get('mi-desempeno', [MiDesempenoController::class, 'index'])->middleware('liga.player')->name('mi-desempeno');

        Route::get('perfil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('perfil', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('perfil/clave', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::view('reglas', 'reglas.index')->name('reglas.index');
        Route::get('pronosticos', [PronosticoController::class, 'edit'])->middleware('liga.player')->name('pronosticos.edit');
        Route::post('pronosticos', [PronosticoController::class, 'update'])->middleware('liga.player')->name('pronosticos.update');

        Route::middleware('liga.admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('users', [LigaUserController::class, 'index'])->name('users.index');
            Route::delete('users/{user}', [LigaUserController::class, 'destroy'])->name('users.destroy');

            Route::get('import', [ImportController::class, 'create'])->name('import.create');
            Route::get('import/plantilla', [ImportController::class, 'template'])->name('import.template');
            Route::post('import/preview', [ImportController::class, 'preview'])->name('import.preview');
            Route::post('import/accept', [ImportController::class, 'accept'])->name('import.accept');
        });
    });
});
