<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterOrangRequest;
use App\Http\Requests\Admin\StoreOrangRequest;
use App\Http\Requests\Admin\UpdateOrangRequest;
use App\Models\Orang;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrangController extends Controller
{
    public function index(FilterOrangRequest $request): View
    {
        $filter = $request->validated();
        $urutkan = $filter['urutkan'] ?? 'nama';
        $arah = $filter['arah'] ?? 'asc';

        $daftarOrang = Orang::query()
            ->when($filter['cari'] ?? null, fn ($query, $cari) => $query->where('nama', 'like', '%'.$cari.'%'))
            ->orderBy($urutkan, $arah)
            ->paginate(25)
            ->withQueryString();

        return view('admin.orang.index', [
            'daftarOrang' => $daftarOrang,
            'filter' => $filter,
        ]);
    }

    public function create(): View
    {
        return view('admin.orang.create');
    }

    public function store(StoreOrangRequest $request): RedirectResponse
    {
        Orang::create($request->validated());

        return redirect()->route('admin.orang.index')->with('sukses', 'Orang berhasil ditambahkan.');
    }

    public function edit(Orang $orang): View
    {
        return view('admin.orang.edit', ['orang' => $orang]);
    }

    public function update(UpdateOrangRequest $request, Orang $orang): RedirectResponse
    {
        $orang->update($request->validated());

        return redirect()->route('admin.orang.index')->with('sukses', 'Orang berhasil diperbarui.');
    }

    public function destroy(Orang $orang): RedirectResponse
    {
        if ($orang->daftarKarya()->exists()) {
            return redirect()->route('admin.orang.index')
                ->with('gagal', 'Data orang tidak bisa dihapus karena masih dipakai oleh karya.');
        }

        $orang->delete();

        return redirect()->route('admin.orang.index')->with('sukses', 'Data orang berhasil dihapus.');
    }
}
