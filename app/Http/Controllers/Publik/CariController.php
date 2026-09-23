<?php

namespace App\Http\Controllers\Publik;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publik\CariRequest;
use App\Models\Karya;
use Illuminate\View\View;

class CariController extends Controller
{
    /**
     * Pencarian publik nomor IBID/judul/penulis (termasuk nama pena)/ISBN.
     * Tanpa kata kunci, menampilkan semua karya Dipublikasikan (browse-all,
     * dipakai juga oleh link "Lihat semua karya" di beranda). Hanya karya
     * dengan status_identitas Dipublikasikan yang pernah muncul di sini.
     */
    public function index(CariRequest $request): View
    {
        $filter = $request->validated();
        $q = $filter['q'] ?? null;
        $urutkan = $filter['urutkan'] ?? 'judul';
        $arah = $filter['arah'] ?? 'asc';

        $daftarKarya = Karya::query()
            ->where('status_identitas', StatusIdentitas::Dipublikasikan)
            ->with([
                'kategori:id,nama',
                'daftarOrang' => fn ($query) => $query->wherePivot('role', PeranOrang::Penulis->value),
            ])
            ->when($q, fn ($query, $q) => $query->where(function ($query) use ($q) {
                $query->where('judul', 'like', '%'.$q.'%')
                    ->orWhere('ibid_number', 'like', '%'.$q.'%')
                    ->orWhere('isbn', 'like', '%'.$q.'%')
                    ->orWhereHas('daftarOrang', function ($query) use ($q) {
                        $query->where('karya_orang.role', PeranOrang::Penulis->value)
                            ->where(function ($query) use ($q) {
                                $query->where('nama', 'like', '%'.$q.'%')
                                    ->orWhere('nama_pena', 'like', '%'.$q.'%');
                            });
                    });
            }))
            ->orderBy($urutkan, $arah)
            ->paginate(12)
            ->withQueryString();

        return view('publik.cari.index', [
            'daftarKarya' => $daftarKarya,
            'filter' => $filter,
        ]);
    }
}
