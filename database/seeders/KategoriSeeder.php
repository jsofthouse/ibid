<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $daftarNama = [
            'Novel',
            'Kumpulan Puisi',
            'Non-Fiksi',
            'Pendidikan',
            'Biografi',
            'Anak & Remaja',
        ];

        foreach ($daftarNama as $nama) {
            Kategori::firstOrCreate(['nama' => $nama]);
        }
    }
}
