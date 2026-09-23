<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterPengajuanRequest;
use App\Models\Pengajuan;
use Illuminate\View\View;

class PengajuanController extends Controller
{
    public function index(FilterPengajuanRequest $request): View
    {
        $filter = $request->validated();
        $urutkan = $filter['urutkan'] ?? 'created_at';
        $arah = $filter['arah'] ?? 'desc';

        $daftarPengajuan = Pengajuan::query()
            ->with('kategori:id,nama')
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy($urutkan, $arah)
            ->paginate(25)
            ->withQueryString();

        return view('admin.pengajuan.index', [
            'daftarPengajuan' => $daftarPengajuan,
            'filter' => $filter,
        ]);
    }

    public function show(Pengajuan $pengajuan): View
    {
        $pengajuan->load(['kategori', 'karya']);

        return view('admin.pengajuan.show', [
            'pengajuan' => $pengajuan,
        ]);
    }
}
