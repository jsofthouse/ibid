<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateFileStreamTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_stream_private_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('naskah/contoh.pdf', 'isi-dummy');

        $this->get('/admin/berkas/naskah/contoh.pdf')
            ->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_stream_existing_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('naskah/contoh.pdf', 'isi-dummy');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/berkas/naskah/contoh.pdf')
            ->assertOk();
    }

    public function test_nonexistent_file_returns_404(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/berkas/naskah/tidak-ada.pdf')
            ->assertNotFound();
    }

    public function test_path_traversal_attempt_is_blocked(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/berkas/..%2F..%2F.env')
            ->assertNotFound();
    }
}
