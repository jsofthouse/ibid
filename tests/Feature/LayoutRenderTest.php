<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Setiap Karya Memiliki Jejak');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Login Superadmin');
    }

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/dashboard')->assertOk()->assertSee($user->name);
    }
}
