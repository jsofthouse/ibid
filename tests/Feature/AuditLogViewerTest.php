<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_tidak_bisa_akses_log_audit(): void
    {
        $this->get('/admin/log')->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_bisa_melihat_daftar_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Kategori::create(['nama' => 'Kategori Uji Log']);

        $response = $this->get('/admin/log');

        $response->assertOk();
        $response->assertSee('Kategori');
    }

    public function test_filter_aksi_tidak_valid_ditolak(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/admin/log?aksi=drop_table')->assertSessionHasErrors('aksi');
    }

    public function test_filter_entitas_menyaring_hasil(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Kategori::create(['nama' => 'Kategori A']);

        $response = $this->get('/admin/log?entitas=kategori');

        $response->assertOk();
        $response->assertSee('Kategori A');
    }
}
