<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_from_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_can_login_and_reach_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_wrong_password_rejected(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'salah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_rate_limited_after_too_many_attempts(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'salah']);
        }

        $response = $this->post('/admin/login', ['email' => $user->email, 'password' => 'salah']);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_login_berhasil_tercatat_di_audit_log(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ]);

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'login_berhasil',
            'entitas' => 'auth',
            'entitas_id' => $user->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_login_gagal_tercatat_di_audit_log_tanpa_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'salah',
        ]);

        $log = AuditLog::query()->where('aksi', 'login_gagal')->firstOrFail();

        $this->assertSame('auth', $log->entitas);
        $this->assertSame($user->id, $log->entitas_id);
        $this->assertNull($log->user_id);
        $this->assertSame(['email' => $user->email], $log->data_after);
        $this->assertStringNotContainsString('salah', json_encode($log->data_after));
    }
}
