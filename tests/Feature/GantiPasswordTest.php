<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GantiPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_tidak_bisa_akses_ganti_password(): void
    {
        $this->get('/admin/password')->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_bisa_ganti_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasiaLama123')]);
        $this->actingAs($user);

        $response = $this->put('/admin/password', [
            'password_saat_ini' => 'rahasiaLama123',
            'password_baru' => 'rahasiaBaru456',
            'password_baru_confirmation' => 'rahasiaBaru456',
        ]);

        $response->assertRedirect(route('admin.password.edit'));
        $this->assertTrue(Hash::check('rahasiaBaru456', $user->fresh()->password));
    }

    public function test_password_saat_ini_salah_ditolak(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasiaLama123')]);
        $this->actingAs($user);

        $response = $this->put('/admin/password', [
            'password_saat_ini' => 'salah-total',
            'password_baru' => 'rahasiaBaru456',
            'password_baru_confirmation' => 'rahasiaBaru456',
        ]);

        $response->assertSessionHasErrors('password_saat_ini');
        $this->assertTrue(Hash::check('rahasiaLama123', $user->fresh()->password));
    }

    public function test_password_baru_tidak_boleh_sama_dengan_lama(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasiaLama123')]);
        $this->actingAs($user);

        $response = $this->put('/admin/password', [
            'password_saat_ini' => 'rahasiaLama123',
            'password_baru' => 'rahasiaLama123',
            'password_baru_confirmation' => 'rahasiaLama123',
        ]);

        $response->assertSessionHasErrors('password_baru');
    }

    public function test_password_baru_wajib_kombinasi_huruf_dan_angka_minimal_8(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasiaLama123')]);
        $this->actingAs($user);

        $response = $this->put('/admin/password', [
            'password_saat_ini' => 'rahasiaLama123',
            'password_baru' => 'pendek1',
            'password_baru_confirmation' => 'pendek1',
        ]);

        $response->assertSessionHasErrors('password_baru');
    }

    public function test_ganti_password_tercatat_di_audit_log_tanpa_menyimpan_nilai_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasiaLama123')]);
        $this->actingAs($user);

        $this->put('/admin/password', [
            'password_saat_ini' => 'rahasiaLama123',
            'password_baru' => 'rahasiaBaru456',
            'password_baru_confirmation' => 'rahasiaBaru456',
        ]);

        $log = AuditLog::query()->where('aksi', 'ganti_password')->firstOrFail();

        $this->assertSame($user->id, $log->user_id);
        $this->assertNull($log->data_before);
        $this->assertNull($log->data_after);
        $this->assertStringNotContainsString('rahasiaBaru456', (string) json_encode($log->toArray()));
    }
}
