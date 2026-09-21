<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karya;
use App\Models\Orang;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalKarya' => Karya::count(),
            'totalIbid' => Karya::whereNotNull('ibid_number')->count(),
            'ibidTahunBerjalan' => Karya::whereNotNull('ibid_number')->whereYear('tanggal_ibid', now()->year)->count(),
            'jumlahOrang' => Orang::count(),
            'denganIsbn' => Karya::whereNotNull('isbn')->count(),
            'tanpaIsbn' => Karya::whereNull('isbn')->count(),
            'perKategori' => Karya::query()
                ->join('kategori', 'kategori.id', '=', 'karya.kategori_id')
                ->selectRaw('kategori.nama as kategori, count(karya.id) as total')
                ->groupBy('kategori.nama')
                ->orderByDesc('total')
                ->get(),
            'perStatusProduksi' => Karya::query()
                ->selectRaw('status_produksi, count(*) as total')
                ->groupBy('status_produksi')
                ->get(),
            'perStatusIdentitas' => Karya::query()
                ->selectRaw('status_identitas, count(*) as total')
                ->groupBy('status_identitas')
                ->get(),
        ]);
    }
}
