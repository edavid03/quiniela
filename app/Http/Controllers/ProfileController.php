<?php

namespace App\Http\Controllers;

use App\Models\Liga;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request, Liga $liga): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request, Liga $liga): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('updateProfile', [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'max:255',
                // El unique real es (liga_id, username): Rule::unique corre una query
                // cruda y NO aplica el LigaScope, por eso el where('liga_id') es obligatorio.
                Rule::unique('users', 'username')
                    ->where(fn ($query) => $query->where('liga_id', Tenancy::id()))
                    ->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return redirect()
            ->route('liga.profile.edit', ['liga' => $liga])
            ->with('status', 'Tus datos fueron actualizados.');
    }

    public function updatePassword(Request $request, Liga $liga): RedirectResponse
    {
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->password = $request->string('password'); // cast 'hashed' en User
        $user->save();

        return redirect()
            ->route('liga.profile.edit', ['liga' => $liga])
            ->with('status', 'Tu contraseña fue actualizada.');
    }
}
