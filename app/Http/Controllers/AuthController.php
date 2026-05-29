<?php

namespace App\Http\Controllers;

use App\Models\Liga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Liga $liga): View
    {
        return view('auth.login', ['liga' => $liga]);
    }

    public function login(Request $request, Liga $liga): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // El login se scopea a la liga del slug: dos ligas pueden tener el mismo
        // username sin colisionar.
        $ok = Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'liga_id' => $liga->id,
        ], $request->boolean('remember'));

        if (! $ok) {
            return back()
                ->withErrors(['username' => 'El usuario o la contraseña no son correctos.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('liga.dashboard', ['liga' => $liga]));
    }

    public function logout(Request $request, Liga $liga): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('liga.login', ['liga' => $liga]);
    }
}
