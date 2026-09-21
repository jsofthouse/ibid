<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_reset_bisa_diakses_dengan_token_di_url(): void
    {
        $this->get('/admin/reset-password/token-contoh?email=test@example.com')->assertOk();
    }

    public function test_reset_dengan_token_valid_berhasil_ganti_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasiaLama123')]);
        $token = Password::broker('users')->createToken($user);

        $response = $this->post('/admin/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordBaru789',
            'password_confirmation' => 'passwordBaru789',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('status');
        $this->assertTrue(Hash::check('passwordBaru789', $user->fresh()->password));
    }

    public function test_reset_dengan_token_salah_ditolak_generik(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasiaLama123')]);

        $response = $this->post('/admin/reset-password', [
            'token' => 'token-ngawur',
            'email' => $user->email,
            'password' => 'passwordBaru789',
            'password_confirmation' => 'passwordBaru789',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('rahasiaLama123', $user->fresh()->password));
    }

    public function test_reset_untuk_email_tidak_terdaftar_ditolak_generik(): void
    {
        $response = $this->post('/admin/reset-password', [
            'token' => 'token-apa-saja',
            'email' => 'tidak-ada@example.com',
            'password' => 'passwordBaru789',
            'password_confirmation' => 'passwordBaru789',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_baru_wajib_kombinasi_huruf_angka_minimal_8(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('users')->createToken($user);

        $this->post('/admin/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'pendek1',
            'password_confirmation' => 'pendek1',
        ])->assertSessionHasErrors('password');
    }

    public function test_reset_berhasil_tercatat_di_audit_log(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('users')->createToken($user);

        $this->post('/admin/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordBaru789',
            'password_confirmation' => 'passwordBaru789',
        ]);

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'reset_password',
            'entitas_id' => $user->id,
        ]);
    }
}
