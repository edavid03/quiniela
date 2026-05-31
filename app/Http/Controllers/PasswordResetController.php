<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\Liga;
use App\Support\PasswordResetTokens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private const GENERIC_STATUS = 'Si la cuenta existe, enviaremos un enlace de recuperacion al correo registrado.';

    public function create(Liga $liga): View
    {
        return view('auth.forgot-password', ['liga' => $liga]);
    }

    public function store(Request $request, Liga $liga, PasswordResetTokens $tokens): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ]);

        $issued = $tokens->createForUsername($liga, $validated['username']);

        if ($issued !== null) {
            [$user, $rawToken] = $issued;

            $url = route('liga.password.reset', [
                'liga' => $liga,
                'username' => $user->username,
                'token' => $rawToken,
            ]);

            Mail::to($user->email)->queue(new PasswordResetMail($liga, $user->username, $url));
        }

        return back()->with('status', self::GENERIC_STATUS);
    }

    public function edit(Request $request, Liga $liga, PasswordResetTokens $tokens): View
    {
        $username = (string) $request->query('username', '');
        $token = (string) $request->query('token', '');

        $user = $tokens->findValidUser($liga, $username, $token);

        if ($user === null) {
            return view('auth.reset-password-invalid', ['liga' => $liga]);
        }

        return view('auth.reset-password', [
            'liga' => $liga,
            'username' => $user->username,
            'token' => $token,
        ]);
    }

    public function update(Request $request, Liga $liga, PasswordResetTokens $tokens): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $tokens->findValidUser($liga, $validated['username'], $validated['token']);

        if ($user === null) {
            return redirect()
                ->route('liga.password.request', ['liga' => $liga])
                ->with('security_alert', 'El enlace de recuperacion no es valido o ya expiro.');
        }

        $user->password = $request->string('password');
        $user->remember_token = Str::random(60);
        $user->save();

        $tokens->deleteFor($user);

        return redirect()
            ->route('liga.login', ['liga' => $liga])
            ->with('status', 'Tu contrasena fue actualizada. Ya puedes iniciar sesion.');
    }
}
