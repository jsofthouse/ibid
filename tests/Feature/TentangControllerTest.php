<?php

namespace Tests\Feature;

use Tests\TestCase;

class TentangControllerTest extends TestCase
{
    public function test_halaman_tentang_bisa_diakses(): void
    {
        $this->get('/tentang')->assertOk();
    }

    public function test_halaman_tentang_menjelaskan_ibid_vs_isbn(): void
    {
        $this->get('/tentang')
            ->assertSee('bukan pengganti ISBN')
            ->assertSee('IRF-YYYY-NNNNNN', false);
    }

    public function test_halaman_tentang_menampilkan_kontak_resmi(): void
    {
        $this->get('/tentang')
            ->assertSee('bukuirfani@gmail.com')
            ->assertSee('www.penerbitirfani.com')
            ->assertSee('@penerbitirfani');
    }

    public function test_halaman_tentang_menautkan_ke_verifikasi_cari_dan_ajukan_penerbitan(): void
    {
        $response = $this->get('/tentang');

        $response->assertSee(route('verifikasi'), escape: false);
        $response->assertSee(route('cari'), escape: false);
        $response->assertSee(route('ajukan-penerbitan'), escape: false);
    }
}
