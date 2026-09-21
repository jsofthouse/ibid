<?php

namespace App\Http\Controllers\Publik;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Http\Controllers\Controller;
use App\Models\Karya;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Halaman identitas karya publik - tampilan & field yang ditampilkan
     * di-whitelist eksplisit per status_identitas (PRD §5), bukan cuma
     * nyembunyiin field pakai class Blade. IBID yang valid tidak pernah
     * 404, entitas_id nomor yang tidak dikenal tetap 404.
     */
    public function show(string $ibid): View
    {
        $karya = Karya::query()
            ->with(['kategori:id,nama', 'daftarOrang:orang.id,nama,nama_pena'])
            ->where('ibid_number', $ibid)
            ->first();

        abort_if($karya === null, 404);

        return match ($karya->status_identitas) {
            StatusIdentitas::Dipublikasikan => $this->halamanDipublikasikan($karya),
            StatusIdentitas::IbidDiterbitkan => $this->halamanPraTerbit($karya),
            StatusIdentitas::TidakAktif, StatusIdentitas::Diarsipkan => $this->halamanTidakAktif($karya),
            // Karya dengan ibid_number pasti sudah lolos dari BelumBerIbid
            // (invarian I1) - cabang ini jaring pengaman saja.
            StatusIdentitas::BelumBerIbid => abort(404),
        };
    }

    private function halamanPraTerbit(Karya $karya): View
    {
        return view('publik.buku.pra-terbit', [
            'judul' => $karya->judul,
            'subjudul' => $karya->subjudul,
            'daftarPenulis' => $this->namaPeran($karya, PeranOrang::Penulis),
            'kategori' => $karya->kategori?->nama,
            'sinopsis' => $karya->sinopsis,
            'ibidNumber' => $karya->ibid_number,
            'coverUrl' => $this->coverUrl($karya),
        ]);
    }

    private function halamanDipublikasikan(Karya $karya): View
    {
        return view('publik.buku.dipublikasikan', [
            'judul' => $karya->judul,
            'subjudul' => $karya->subjudul,
            'daftarPeran' => collect(PeranOrang::cases())
                ->mapWithKeys(fn (PeranOrang $peran) => [$peran->label() => $this->namaPeran($karya, $peran)])
                ->filter(fn ($daftar) => $daftar->isNotEmpty()),
            'kategori' => $karya->kategori?->nama,
            'sinopsis' => $karya->sinopsis,
            'ibidNumber' => $karya->ibid_number,
            'isbn' => $karya->isbn,
            'tahunTerbit' => $karya->tahun_terbit,
            'kotaTerbit' => $karya->kota_terbit,
            'edisi' => $karya->edisi,
            'bahasa' => $karya->bahasa,
            'jumlahHalaman' => $karya->jumlah_halaman,
            'ukuran' => $karya->ukuran,
            'coverUrl' => $this->coverUrl($karya),
        ]);
    }

    private function halamanTidakAktif(Karya $karya): View
    {
        $pernahDipublikasikan = $karya->tanggal_dipublikasikan !== null;

        return view('publik.buku.tidak-aktif', [
            'judul' => $pernahDipublikasikan ? $karya->judul : null,
            'ibidNumber' => $karya->ibid_number,
        ]);
    }

    private function namaPeran(Karya $karya, PeranOrang $peran): Collection
    {
        return $karya->daftarOrang
            ->filter(fn ($orang) => $orang->pivot->role === $peran)
            ->map(fn ($orang) => $orang->nama_pena ?: $orang->nama)
            ->values();
    }

    private function coverUrl(Karya $karya): ?string
    {
        return $karya->cover_path ? asset('storage-karya/'.$karya->cover_path) : null;
    }
}
