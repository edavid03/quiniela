<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_shows_current_data(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['name' => 'Juan Perez', 'username' => 'juanp']);

        $this->actingAs($user)
            ->get(route('liga.profile.edit', $liga))
            ->assertOk()
            ->assertSee('Mi cuenta')
            ->assertSee('Juan Perez')
            ->assertSee('juanp');
    }

    public function test_user_can_update_name_and_username(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['name' => 'Viejo', 'username' => 'viejo']);

        $this->actingAs($user)
            ->put(route('liga.profile.update', $liga), [
                'name' => 'Nuevo Nombre',
                'username' => 'nuevo_user',
            ])
            ->assertRedirect(route('liga.profile.edit', $liga))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nuevo Nombre',
            'username' => 'nuevo_user',
        ]);
    }

    public function test_username_must_be_unique_within_the_same_liga(): void
    {
        $liga = $this->createLiga();
        $this->ligaUser($liga, ['username' => 'tomado']);
        $user = $this->ligaUser($liga, ['username' => 'mio']);

        $this->actingAs($user)
            ->put(route('liga.profile.update', $liga), [
                'name' => 'Yo',
                'username' => 'tomado',
            ])
            ->assertSessionHasErrors('username', null, 'updateProfile');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'mio']);
    }

    public function test_same_username_is_allowed_in_a_different_liga(): void
    {
        $otraLiga = $this->createLiga();
        $this->ligaUser($otraLiga, ['username' => 'compartido']);

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['username' => 'mio']);

        $this->actingAs($user)
            ->put(route('liga.profile.update', $liga), [
                'name' => 'Yo',
                'username' => 'compartido',
            ])
            ->assertSessionDoesntHaveErrors('username');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'compartido']);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['password' => Hash::make('clave-actual')]);

        $this->actingAs($user)
            ->put(route('liga.profile.password', $liga), [
                'current_password' => 'clave-actual',
                'password' => 'clave-nueva-9',
                'password_confirmation' => 'clave-nueva-9',
            ])
            ->assertRedirect(route('liga.profile.edit', $liga))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('clave-nueva-9', $user->fresh()->password));
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga, ['password' => Hash::make('clave-actual')]);

        $this->actingAs($user)
            ->put(route('liga.profile.password', $liga), [
                'current_password' => 'incorrecta',
                'password' => 'clave-nueva-9',
                'password_confirmation' => 'clave-nueva-9',
            ])
            ->assertSessionHasErrors('current_password', null, 'updatePassword');

        $this->assertTrue(Hash::check('clave-actual', $user->fresh()->password));
    }
}
