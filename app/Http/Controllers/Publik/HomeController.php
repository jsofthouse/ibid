<?php

namespace App\Http\Controllers\Publik;

use App\Enums\PeranOrang;
use App\Http\Controllers\Controller;
use App\Models\Karya;
use App\Models\KaryaOrang;
use App\Models\Kategori;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('public.home', [
            'jumlahKaryaTerdaftar' => Karya::query()->whereNotNull('ibid_number')->count(),
            'jumlahPenulis' => KaryaOrang::query()
                ->where('role', PeranOrang::Penulis->value)
                ->distinct('orang_id')
                ->count('orang_id'),
            'jumlahKategori' => Kategori::query()->count(),
        ]);
    }
}
