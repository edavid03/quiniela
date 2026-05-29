<?php

namespace Tests\Feature;

use App\Mail\LigaInvitationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_accept_creates_users_and_queues_invitations(): void
    {
        Mail::fake();

        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);

        $this->actingAs($admin)
            ->post(route('liga.admin.import.accept', $liga), [
                'rows' => [
                    ['email' => 'ana@correo.test', 'username' => 'ana', 'name' => 'Ana'],
                    ['email' => 'beto@correo.test', 'username' => 'beto', 'name' => 'Beto'],
                ],
            ])
            ->assertRedirect(route('liga.admin.users.index', $liga));

        $this->assertDatabaseHas('users', [
            'liga_id' => $liga->id,
            'email' => 'ana@correo.test',
            'role' => 'liga_user',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'beto@correo.test']);
        $this->assertDatabaseCount('invitations', 2);

        Mail::assertQueued(LigaInvitationMail::class, 2);
    }

    public function test_accept_skips_existing_users(): void
    {
        Mail::fake();

        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);
        $this->ligaUser($liga, ['email' => 'ana@correo.test', 'username' => 'ana']);

        $this->actingAs($admin)
            ->post(route('liga.admin.import.accept', $liga), [
                'rows' => [
                    ['email' => 'ana@correo.test', 'username' => 'ana', 'name' => 'Ana'],
                    ['email' => 'carlos@correo.test', 'username' => 'carlos', 'name' => 'Carlos'],
                ],
            ])
            ->assertRedirect(route('liga.admin.users.index', $liga));

        Mail::assertQueued(LigaInvitationMail::class, 1);
        $this->assertDatabaseHas('users', ['email' => 'carlos@correo.test']);
    }

    public function test_preview_flags_invalid_rows(): void
    {
        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);

        $csv = "email,username,name\nana@correo.test,ana,Ana\nmal-email,beto,Beto\n";
        $file = UploadedFile::fake()->createWithContent('usuarios.csv', $csv);

        $this->actingAs($admin)
            ->post(route('liga.admin.import.preview', $liga), ['file' => $file])
            ->assertOk()
            ->assertSee('Email inválido');
    }

    public function test_ajax_preview_returns_only_the_rows_fragment(): void
    {
        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);

        $csv = "email,username,name\nana@correo.test,ana,Ana\n";
        $file = UploadedFile::fake()->createWithContent('usuarios.csv', $csv);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('liga.admin.import.preview', $liga), ['file' => $file]);

        $response->assertOk()
            ->assertSee('ana@correo.test')           // contiene los datos de la fila
            ->assertSee('Confirmar y enviar invitaciones')
            ->assertDontSee('</html>', false)         // NO es la pagina completa...
            ->assertDontSee('Ranking');               // ...ni trae el nav del layout
    }

    public function test_admin_can_download_the_template(): void
    {
        $liga = $this->createLiga();
        $admin = $this->ligaAdmin($liga);

        $this->actingAs($admin)
            ->get(route('liga.admin.import.template', $liga))
            ->assertOk()
            ->assertDownload('plantilla-usuarios.xlsx');
    }

    public function test_non_admin_cannot_access_import(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get(route('liga.admin.import.create', $liga))
            ->assertRedirect(route('liga.dashboard', $liga));
    }
}
