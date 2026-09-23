<?php

namespace App\Http\Controllers\Publik;

use App\Enums\StatusPengajuan;
use App\Exceptions\NaskahUploadException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publik\AjukanPenerbitanRequest;
use App\Mail\PengajuanDiterimaMail;
use App\Models\Kategori;
use App\Models\Pengajuan;
use App\Services\NaskahUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class AjukanPenerbitanController extends Controller
{
    /**
     * Field pengajuan yang boleh diisi lewat form publik. status dan
     * karya_id SENGAJA tidak masuk sini - diisi eksplisit di store()
     * (status) atau lewat PengajuanService saat admin approve
     * (karya_id), tidak pernah lewat input request.
     */
    private const KOLOM_IZIN = [
        'nama', 'nama_pena', 'email', 'nomor_wa', 'alamat', 'kota', 'provinsi',
        'judul', 'kategori_id', 'sinopsis',
    ];

    public function __construct(private readonly NaskahUploadService $naskahUploadService) {}

    public function create(): View
    {
        return view('publik.ajukan-penerbitan.index', [
            'daftarKategori' => Kategori::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(AjukanPenerbitanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $naskahPath = $this->naskahUploadService->unggah($request->file('naskah'));
        } catch (NaskahUploadException $e) {
            return back()->withInput()->withErrors(['naskah' => $e->getMessage()]);
        }

        $suratKeaslianPath = null;

        if ($request->hasFile('surat_keaslian')) {
            try {
                $suratKeaslianPath = $this->naskahUploadService->unggah($request->file('surat_keaslian'));
            } catch (NaskahUploadException $e) {
                return back()->withInput()->withErrors(['surat_keaslian' => $e->getMessage()]);
            }
        }

        $dataPengajuan = collect($data)->only(self::KOLOM_IZIN)->toArray();
        $dataPengajuan['naskah_path'] = $naskahPath;
        $dataPengajuan['surat_keaslian_path'] = $suratKeaslianPath;
        $dataPengajuan['status'] = StatusPengajuan::Baru;

        $pengajuan = Pengajuan::create($dataPengajuan);

        try {
            Mail::to($pengajuan->email)->send(new PengajuanDiterimaMail($pengajuan));
        } catch (Throwable $e) {
            // Kegagalan kirim email tidak boleh menggagalkan pengajuan yang
            // sudah tersimpan - dicatat saja supaya bisa ditindaklanjuti.
            Log::error('Gagal mengirim email konfirmasi pengajuan.', [
                'pengajuan_id' => $pengajuan->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('ajukan-penerbitan')
            ->with('sukses', 'Pengajuan penerbitan berhasil dikirim. Konfirmasi sudah kami kirim ke email Anda.');
    }
}
