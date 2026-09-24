<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Exceptions\CoverUploadException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterKaryaRequest;
use App\Http\Requests\Admin\StoreKaryaRequest;
use App\Http\Requests\Admin\UpdateKaryaRequest;
use App\Models\AuditLog;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Services\CoverUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KaryaController extends Controller
{
    private const KOLOM_METADATA = [
        'judul', 'subjudul', 'kategori_id', 'isbn', 'tahun_terbit', 'kota_terbit',
        'edisi', 'bahasa', 'jumlah_halaman', 'ukuran', 'sinopsis', 'kata_kunci',
        'tampil_pra_terbit', 'tanggal_dibuat', 'tanggal_diterbitkan',
    ];

    public function index(FilterKaryaRequest $request): View
    {
        $filter = $request->validated();
        $urutkan = $filter['urutkan'] ?? 'created_at';
        $arah = $filter['arah'] ?? 'desc';

        $daftarKarya = Karya::query()
            ->with([
                'kategori:id,nama',
                'daftarOrang' => fn ($query) => $query->wherePivot('role', PeranOrang::Penulis->value),
            ])
            ->when($filter['cari'] ?? null, fn ($query, $cari) => $query->where('judul', 'like', '%'.$cari.'%'))
            ->when($filter['status_produksi'] ?? null, fn ($query, $status) => $query->where('status_produksi', $status))
            ->when($filter['status_identitas'] ?? null, fn ($query, $status) => $query->where('status_identitas', $status))
            ->orderBy($urutkan, $arah)
            ->paginate(25)
            ->withQueryString();

        return view('admin.karya.index', [
            'daftarKarya' => $daftarKarya,
            'filter' => $filter,
        ]);
    }

    public function create(): View
    {
        return view('admin.karya.create', [
            'daftarKategori' => Kategori::orderBy('nama')->get(['id', 'nama']),
            'daftarPilihanOrang' => $this->daftarPilihanOrang(),
            'karya' => null,
            'peranTerpilih' => [],
        ]);
    }

    public function store(StoreKaryaRequest $request, CoverUploadService $coverUploadService): RedirectResponse
    {
        $data = $this->dataMetadata($request->validated());

        if ($request->hasFile('cover')) {
            try {
                $data['cover_path'] = $coverUploadService->unggah($request->file('cover'))['cover_path'];
            } catch (CoverUploadException $e) {
                return back()->withInput()->withErrors(['cover' => $e->getMessage()]);
            }
        }

        $karya = Karya::create($data);

        $this->sinkronkanPeran($karya, $request->validated());

        return redirect()->route('admin.karya.show', $karya)->with('sukses', 'Karya berhasil ditambahkan.');
    }

    public function show(Karya $karya): View
    {
        $karya->load(['kategori', 'daftarOrang']);

        $riwayat = AuditLog::query()
            ->with('user:id,name')
            ->where('entitas', 'karya')
            ->where('entitas_id', $karya->id)
            ->latest('created_at')
            ->paginate(20, pageName: 'halaman_riwayat')
            ->withQueryString();

        return view('admin.karya.show', [
            'karya' => $karya,
            'riwayat' => $riwayat,
        ]);
    }

    public function edit(Karya $karya): View
    {
        $karya->load('daftarOrang');

        $peranTerpilih = [];
        foreach (PeranOrang::cases() as $peran) {
            $peranTerpilih[$peran->value] = $karya->daftarOrang
                ->filter(fn ($orang) => $orang->pivot->role === $peran)
                ->pluck('id')
                ->all();
        }

        return view('admin.karya.edit', [
            'karya' => $karya,
            'daftarKategori' => Kategori::orderBy('nama')->get(['id', 'nama']),
            'daftarPilihanOrang' => $this->daftarPilihanOrang(),
            'peranTerpilih' => $peranTerpilih,
        ]);
    }

    public function update(UpdateKaryaRequest $request, Karya $karya, CoverUploadService $coverUploadService): RedirectResponse
    {
        $data = $this->dataMetadata($request->validated());

        if ($request->hasFile('cover')) {
            try {
                $coverBaru = $coverUploadService->unggah($request->file('cover'))['cover_path'];
            } catch (CoverUploadException $e) {
                return back()->withInput()->withErrors(['cover' => $e->getMessage()]);
            }

            $coverLama = $karya->cover_path;
            $data['cover_path'] = $coverBaru;

            if ($coverLama) {
                $coverUploadService->hapus($coverLama, $coverUploadService->thumbnailUntuk($coverLama));
            }
        }

        $karya->update($data);

        $this->sinkronkanPeran($karya, $request->validated());

        return redirect()->route('admin.karya.show', $karya)->with('sukses', 'Karya berhasil diperbarui.');
    }

    public function destroy(Karya $karya): RedirectResponse
    {
        if ($karya->status_identitas !== StatusIdentitas::BelumBerIbid) {
            return redirect()->route('admin.karya.index')
                ->with('gagal', 'Karya yang sudah ber-IBID tidak bisa dihapus - nonaktifkan atau arsipkan sebagai gantinya.');
        }

        $karya->delete();

        return redirect()->route('admin.karya.index')->with('sukses', 'Karya berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function dataMetadata(array $validated): array
    {
        return collect($validated)->only(self::KOLOM_METADATA)->toArray();
    }

    /**
     * Pilihan orang per box peran. Soft default: grup 'sesuai' berisi orang
     * yang declared role-nya (orang_role) cocok dengan peran box, grup 'lain'
     * berisi sisanya. Semua orang tetap bisa dipilih di tiap box, karena
     * assignment karya_orang boleh beda dari orang_role.
     *
     * @return array<string, array{sesuai: Collection<int, Orang>, lain: Collection<int, Orang>}>
     */
    private function daftarPilihanOrang(): array
    {
        $semuaOrang = Orang::query()
            ->with('daftarRole:id,orang_id,role')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nama_pena']);

        $hasil = [];

        foreach (PeranOrang::cases() as $peran) {
            [$sesuai, $lain] = $semuaOrang->partition(
                fn (Orang $orang) => $orang->daftarRole->contains(fn ($role) => $role->role === $peran),
            );

            $hasil[$peran->value] = ['sesuai' => $sesuai->values(), 'lain' => $lain->values()];
        }

        return $hasil;
    }

    private function sinkronkanPeran(Karya $karya, array $data): void
    {
        foreach (PeranOrang::cases() as $peran) {
            $karya->daftarOrang()->wherePivot('role', $peran->value)->detach();
        }

        foreach (PeranOrang::cases() as $peran) {
            $daftarId = collect($data[$peran->value] ?? [])->unique();

            foreach ($daftarId as $orangId) {
                $karya->daftarOrang()->attach($orangId, ['role' => $peran->value]);
            }
        }
    }
}
