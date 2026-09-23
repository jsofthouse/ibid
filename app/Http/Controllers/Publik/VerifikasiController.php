<?php

namespace App\Http\Controllers\Publik;

use App\Enums\StatusIdentitas;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publik\VerifikasiRequest;
use App\Models\Karya;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    /**
     * Verifikasi nomor IBID publik. Field yang diekspos sengaja diminimalkan
     * (cuma nomor + status) - tidak ada judul/penulis/data lain, beda dengan
     * /buku/{ibid} yang memang halaman identitas penuh.
     */
    public function index(VerifikasiRequest $request): View
    {
        $nomor = $request->validated('nomor');
        $hasil = null;

        if (filled($nomor)) {
            $karya = Karya::query()
                ->where('ibid_number', strtoupper(trim($nomor)))
                ->first(['ibid_number', 'status_identitas']);

            $hasil = match ($karya?->status_identitas) {
                StatusIdentitas::Dipublikasikan => ['status' => 'valid', 'ibidNumber' => $karya->ibid_number],
                StatusIdentitas::IbidDiterbitkan => ['status' => 'dalam_proses', 'ibidNumber' => $karya->ibid_number],
                default => ['status' => 'tidak_ditemukan'],
            };
        }

        return view('publik.verifikasi.index', [
            'nomor' => $nomor,
            'hasil' => $hasil,
        ]);
    }
}
