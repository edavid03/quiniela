<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Liga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(Liga $liga, string $token): View
    {
        $invitation = $this->findUsable($liga, $token);

        if ($invitation === null) {
            return view('invitaciones.invalida', ['liga' => $liga]);
        }

        return view('invitaciones.set-password', [
            'liga' => $liga,
            'token' => $token,
            'invitation' => $invitation,
        ]);
    }

    public function accept(Request $request, Liga $liga, string $token): RedirectResponse
    {
        $invitation = $this->findUsable($liga, $token);

        if ($invitation === null) {
            return redirect()
                ->route('liga.login', ['liga' => $liga])
                ->with('security_alert', 'La invitación no es válida o ya expiró.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $invitation->user;

        // El cast 'hashed' del modelo hashea al asignar.
        $user->password = $request->string('password');
        $user->email_verified_at = now();
        $user->save();

        // Single-use: una vez aceptada, findUsable() la descarta.
        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('liga.dashboard', ['liga' => $liga]);
    }

    private function findUsable(Liga $liga, string $token): ?Invitation
    {
        return Invitation::query()
            ->where('liga_id', $liga->id)
            ->where('token', Invitation::hashToken($token))
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
