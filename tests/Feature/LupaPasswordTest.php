<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LupaPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_lupa_password_bisa_diakses_guest(): void
    {
        $this->get('/admin/lupa-password')->assertOk();
    }

    public function test_email_terdaftar_dikirimi_notifikasi_reset(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->post('/admin/lupa-password', ['email' => $user->email]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_email_tidak_terdaftar_tetap_dapat_respons_generik_sama(): void
    {
        Notification::fake();

        $response = $this->post('/admin/lupa-password', ['email' => 'tidak-ada@example.com']);

        $response->assertRedirect();
        $pesanTidakTerdaftar = session('status');
        Notification::assertNothingSent();

        $user = User::factory()->create();
        $this->post('/admin/lupa-password', ['email' => $user->email]);
        $pesanTerdaftar = session('status');

        $this->assertSame($pesanTidakTerdaftar, $pesanTerdaftar);
    }

    public function test_permintaan_reset_tercatat_di_audit_log_untuk_email_terdaftar_maupun_tidak(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/admin/lupa-password', ['email' => $user->email]);
        $this->post('/admin/lupa-password', ['email' => 'tidak-ada@example.com']);

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'lupa_password_diminta',
            'entitas_id' => $user->id,
        ]);

        $log = AuditLog::query()->where('aksi', 'lupa_password_diminta')->whereNull('entitas_id')->first();
        $this->assertNotNull($log);
    }

    public function test_email_wajib_diisi_dan_format_valid(): void
    {
        $this->post('/admin/lupa-password', ['email' => ''])->assertSessionHasErrors('email');
        $this->post('/admin/lupa-password', ['email' => 'bukan-email'])->assertSessionHasErrors('email');
    }

    /**
     * Sengaja TIDAK pakai Notification::fake() - notifikasi ResetPassword
     * asli harus benar-benar bisa dirender (termasuk build link-nya lewat
     * route()) tanpa error. Notification::fake() akan menyembunyikan bug
     * di proses render ini (pernah kejadian: link pakai nama route bawaan
     * Laravel "password.reset" yang tidak ada karena route kita diberi
     * prefix "admin.").
     */
    public function test_notifikasi_reset_benar_benar_bisa_dirender_tanpa_error(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/admin/lupa-password', ['email' => $user->email]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status');
    }
}
