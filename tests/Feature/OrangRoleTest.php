<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrangRoleTest extends TestCase
{
    use RefreshDatabase;

    private function masukSebagaiAdmin(): void
    {
        $this->actingAs(User::factory()->create());
    }

    public function test_orang_baru_dengan_dua_role_tampil_di_tab_dan_jadi_default_picker(): void
    {
        $this->masukSebagaiAdmin();
        $penulisSaja = Orang::create(['nama' => 'Penulis Saja']);
        $penulisSaja->sinkronkanRole([PeranOrang::Penulis]);

        $this->post('/admin/orang', ['nama' => 'Dewi Lestari', 'roles' => ['editor', 'penerjemah']])
            ->assertRedirect(route('admin.orang.index'));

        $dewi = Orang::firstWhere('nama', 'Dewi Lestari');
        $this->assertDatabaseHas('orang_role', ['orang_id' => $dewi->id, 'role' => 'editor']);
        $this->assertDatabaseHas('orang_role', ['orang_id' => $dewi->id, 'role' => 'penerjemah']);
        $this->assertDatabaseMissing('orang_role', ['orang_id' => $dewi->id, 'role' => 'penulis']);

        $this->get('/admin/orang')->assertOk()->assertSee('Dewi Lestari')->assertSee('Penulis Saja');
        $this->get('/admin/orang?role=editor')->assertOk()->assertSee('Dewi Lestari')->assertDontSee('Penulis Saja');
        $this->get('/admin/orang?role=penerjemah')->assertOk()->assertSee('Dewi Lestari')->assertDontSee('Penulis Saja');
        $this->get('/admin/orang?role=penulis')->assertOk()->assertDontSee('Dewi Lestari')->assertSee('Penulis Saja');
        $this->get('/admin/orang?role=kontributor')->assertOk()->assertDontSee('Dewi Lestari');

        $pilihan = $this->get('/admin/karya/create')->assertOk()->viewData('daftarPilihanOrang');

        $this->assertContains($dewi->id, $pilihan['editor']->pluck('id')->all());
        $this->assertContains($dewi->id, $pilihan['penerjemah']->pluck('id')->all());
        $this->assertNotContains($dewi->id, $pilihan['penulis']->pluck('id')->all());
        $this->assertNotContains($dewi->id, $pilihan['kontributor']->pluck('id')->all());
    }

    public function test_tab_role_dan_pencarian_bisa_digabung(): void
    {
        $this->masukSebagaiAdmin();
        $budi = Orang::create(['nama' => 'Budi Editor']);
        $budi->sinkronkanRole([PeranOrang::Editor]);
        $sinta = Orang::create(['nama' => 'Sinta Editor']);
        $sinta->sinkronkanRole([PeranOrang::Editor]);

        $this->get('/admin/orang?role=editor&cari=Sinta')
            ->assertOk()
            ->assertSee('Sinta Editor')
            ->assertDontSee('Budi Editor');
    }

    public function test_filter_role_di_luar_whitelist_ditolak(): void
    {
        $this->masukSebagaiAdmin();

        $this->get('/admin/orang?role=admin')->assertSessionHasErrors('role');
    }

    public function test_orang_tanpa_role_tetap_tampil_di_semua_dan_kosong_di_tab_role(): void
    {
        $this->masukSebagaiAdmin();
        $this->get('/admin/orang/create')->assertOk()->assertSee('Tugas');
        $this->post('/admin/orang', ['nama' => 'Tanpa Tugas'])->assertRedirect(route('admin.orang.index'));

        $this->assertDatabaseCount('orang_role', 0);
        $this->get('/admin/orang')->assertSee('Tanpa Tugas');
        $this->get('/admin/orang?role=penulis')->assertDontSee('Tanpa Tugas');
    }

    public function test_edit_orang_menyinkronkan_role_tambah_dan_kurang(): void
    {
        $this->masukSebagaiAdmin();
        $orang = Orang::create(['nama' => 'Sinkron']);
        $orang->sinkronkanRole([PeranOrang::Penulis, PeranOrang::Editor]);

        $this->put("/admin/orang/{$orang->id}", ['nama' => 'Sinkron', 'roles' => ['editor', 'kontributor']])
            ->assertRedirect(route('admin.orang.index'));

        $this->assertEqualsCanonicalizing(
            ['editor', 'kontributor'],
            $orang->daftarRole()->get()->map(fn ($role) => $role->role->value)->all(),
        );

        $this->put("/admin/orang/{$orang->id}", ['nama' => 'Sinkron'])->assertRedirect(route('admin.orang.index'));

        $this->assertDatabaseCount('orang_role', 0);
    }

    public function test_form_edit_menandai_checkbox_role_yang_sudah_di_declare(): void
    {
        $this->masukSebagaiAdmin();
        $orang = Orang::create(['nama' => 'Centang']);
        $orang->sinkronkanRole([PeranOrang::Editor]);

        $html = $this->get("/admin/orang/{$orang->id}/edit")->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/value="editor"\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="penulis"\s+checked/', $html);
    }

    public function test_roles_divalidasi_whitelist_dan_tidak_masuk_mass_assignment(): void
    {
        $this->masukSebagaiAdmin();

        $this->post('/admin/orang', ['nama' => 'Jahat', 'roles' => ['superadmin']])
            ->assertSessionHasErrors('roles.0');
        $this->post('/admin/orang', ['nama' => 'Jahat', 'roles' => 'penulis'])
            ->assertSessionHasErrors('roles');
        $this->post('/admin/orang', ['nama' => 'Jahat', 'roles' => ['penulis', 'penulis']])
            ->assertSessionHasErrors('roles.0');

        $this->assertNull(Orang::firstWhere('nama', 'Jahat'));
        $this->assertDatabaseCount('orang_role', 0);
    }

    public function test_backfill_mengisi_orang_role_dari_karya_orang_dan_idempotent(): void
    {
        $kategori = Kategori::create(['nama' => 'Novel']);
        $karyaSatu = Karya::create(['judul' => 'Karya Satu', 'kategori_id' => $kategori->id]);
        $karyaDua = Karya::create(['judul' => 'Karya Dua', 'kategori_id' => $kategori->id]);

        $lama = Orang::create(['nama' => 'Orang Lama']);
        $lama->daftarKarya()->attach($karyaSatu->id, ['role' => 'penulis']);
        $lama->daftarKarya()->attach($karyaDua->id, ['role' => 'penulis']);
        $lama->daftarKarya()->attach($karyaSatu->id, ['role' => 'editor']);
        $tanpaKarya = Orang::create(['nama' => 'Tanpa Karya']);

        $this->assertDatabaseCount('orang_role', 0);

        $backfill = require database_path('migrations/2026_09_24_100002_backfill_orang_role_dari_karya_orang.php');

        $backfill->up();

        $this->assertDatabaseCount('orang_role', 2);
        $this->assertDatabaseHas('orang_role', ['orang_id' => $lama->id, 'role' => 'penulis']);
        $this->assertDatabaseHas('orang_role', ['orang_id' => $lama->id, 'role' => 'editor']);
        $this->assertDatabaseMissing('orang_role', ['orang_id' => $tanpaKarya->id]);

        $backfill->up();

        $this->assertDatabaseCount('orang_role', 2);
    }

    public function test_assignment_karya_tidak_diblokir_oleh_declared_role(): void
    {
        $this->masukSebagaiAdmin();
        $kategori = Kategori::create(['nama' => 'Novel']);
        $orang = Orang::create(['nama' => 'Cuma Penulis']);
        $orang->sinkronkanRole([PeranOrang::Penulis]);

        $this->post('/admin/karya', [
            'judul' => 'Karya Beda Role',
            'kategori_id' => $kategori->id,
            'editor' => [$orang->id],
        ])->assertSessionHasNoErrors();

        $karya = Karya::firstWhere('judul', 'Karya Beda Role');
        $this->assertDatabaseHas('karya_orang', ['karya_id' => $karya->id, 'orang_id' => $orang->id, 'role' => 'editor']);
        $this->assertDatabaseMissing('orang_role', ['orang_id' => $orang->id, 'role' => 'editor']);
    }

    public function test_picker_hanya_menampilkan_orang_sesuai_role_dan_yang_sudah_terpasang(): void
    {
        $this->masukSebagaiAdmin();
        $kategori = Kategori::create(['nama' => 'Novel']);
        $editor = Orang::create(['nama' => 'Editor Asli']);
        $editor->sinkronkanRole([PeranOrang::Editor]);
        $penulisTerpasang = Orang::create(['nama' => 'Penulis Terpasang Jadi Editor']);
        $penulisTerpasang->sinkronkanRole([PeranOrang::Penulis]);
        $penulisLain = Orang::create(['nama' => 'Penulis Lain']);
        $penulisLain->sinkronkanRole([PeranOrang::Penulis]);
        $polos = Orang::create(['nama' => 'Tanpa Role']);

        $karya = Karya::create(['judul' => 'Karya Picker', 'kategori_id' => $kategori->id]);
        $karya->daftarOrang()->attach($penulisTerpasang->id, ['role' => 'editor']);

        $response = $this->get("/admin/karya/{$karya->id}/edit")
            ->assertOk()
            ->assertDontSee('Orang lain')
            ->assertDontSee('<optgroup', false);

        $pilihan = $response->viewData('daftarPilihanOrang');

        // Editor: declared editor + yang sudah terpasang di karya ini; penulis lain & tanpa role tidak ikut.
        $this->assertEqualsCanonicalizing(
            [$editor->id, $penulisTerpasang->id],
            $pilihan['editor']->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$penulisTerpasang->id, $penulisLain->id],
            $pilihan['penulis']->pluck('id')->all(),
        );
        $this->assertSame([], $pilihan['kontributor']->pluck('id')->all());
        $this->assertSame([], $pilihan['penerjemah']->pluck('id')->all());
        $this->assertMatchesRegularExpression(
            '/value="'.$penulisTerpasang->id.'"\s+selected/',
            $response->getContent(),
        );
    }

    public function test_simpan_karya_tidak_melepas_orang_terpasang_yang_declared_role_nya_sudah_dicabut(): void
    {
        $this->masukSebagaiAdmin();
        $kategori = Kategori::create(['nama' => 'Novel']);
        $orang = Orang::create(['nama' => 'Editor Dicabut']);
        $orang->sinkronkanRole([PeranOrang::Penulis]);
        $karya = Karya::create(['judul' => 'Karya Lama', 'kategori_id' => $kategori->id]);
        $karya->daftarOrang()->attach($orang->id, ['role' => 'editor']);

        $html = $this->get("/admin/karya/{$karya->id}/edit")->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/value="'.$orang->id.'"\s+selected/', $html);

        $this->put("/admin/karya/{$karya->id}", [
            'judul' => 'Karya Lama',
            'kategori_id' => $kategori->id,
            'editor' => [$orang->id],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('karya_orang', ['karya_id' => $karya->id, 'orang_id' => $orang->id, 'role' => 'editor']);
    }

    public function test_pilihan_peran_tetap_terpilih_setelah_validasi_gagal(): void
    {
        $this->masukSebagaiAdmin();
        $orang = Orang::create(['nama' => 'Pilihan Lama']);
        $orang->sinkronkanRole([PeranOrang::Editor]);

        $this->from('/admin/karya/create')
            ->post('/admin/karya', ['judul' => '', 'editor' => [$orang->id]])
            ->assertRedirect('/admin/karya/create');

        $html = $this->get('/admin/karya/create')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/value="'.$orang->id.'"\s+selected/', $html);
    }
}
