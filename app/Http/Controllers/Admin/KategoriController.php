<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterKategoriRequest;
use App\Http\Requests\Admin\StoreKategoriRequest;
use App\Http\Requests\Admin\UpdateKategoriRequest;
use App\Models\Karya;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(FilterKategoriRequest $request): View
    {
        $filter = $request->validated();
        $urutkan = $filter['urutkan'] ?? 'nama';
        $arah = $filter['arah'] ?? 'asc';

        $daftarKategori = Kategori::query()
            ->when($filter['cari'] ?? null, fn ($query, $cari) => $query->where('nama', 'like', '%'.$cari.'%'))
            ->orderBy($urutkan, $arah)
            ->paginate(25)
            ->withQueryString();

        return view('admin.kategori.index', [
            'daftarKategori' => $daftarKategori,
            'filter' => $filter,
        ]);
    }

    public function create(): View
    {
        return view('admin.kategori.create');
    }

    public function store(StoreKategoriRequest $request): RedirectResponse
    {
        Kategori::create($request->validated());

        return redirect()->route('admin.kategori.index')->with('sukses', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Kategori $kategori): View
    {
        return view('admin.kategori.edit', ['kategori' => $kategori]);
    }

    public function update(UpdateKategoriRequest $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        return redirect()->route('admin.kategori.index')->with('sukses', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if (Karya::query()->where('kategori_id', $kategori->id)->exists()) {
            return redirect()->route('admin.kategori.index')
                ->with('gagal', 'Kategori tidak bisa dihapus karena masih dipakai oleh karya.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('sukses', 'Kategori berhasil dihapus.');
    }
}
