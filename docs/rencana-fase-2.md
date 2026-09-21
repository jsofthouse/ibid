---
title: IBID — Rencana Fase 2 (Admin Panel Core)
status: Fase 2a (langkah 0-8) SELESAI & di-push ke main 2026-09-21 (commit 678c4c1). Sisanya masuk Fase 2b — lihat bagian "Fase 2b" di bawah.
tanggal: 2026-09-21
---

# Konteks
Fase 1 (fondasi) selesai. Fase 2 = Admin Panel Core: semua fitur inti di sisi superadmin. Disusun dari route list PRD §10 + hasil diskusi. Sisi publik (form pengajuan, cari, verifikasi, Tentang) masuk Fase 3, kecuali halaman `/buku/{ibid}` yang wajib ada di Fase 2 (QR harus selalu bisa dibuka). PRD (`PRD.md`) adalah sumber kebenaran; dokumen ini = alasan keputusan + urutan kerja.

# Alur end-to-end
Pengajuan Baru → Diproses → **Disetujui** → convert jadi karya → **Generate IBID (+ QR)** → editor sematkan QR di layout buku → Dalam Proses (edit naskah, layout, cetak) → Diterbitkan → **Publikasikan**.
IBID sengaja lahir SEBELUM proses editing/layout supaya QR bisa disematkan editor di dalam buku (posisi penempatan terserah klien). Selama menunggu terbit dan tanpa kendala, penulis bisa mempromosikan karyanya lewat link/QR IBID (halaman pra-terbit). Kalau penerbitan tidak jadi, superadmin membatalkan IBID.

# Keputusan terkunci (2026-09-21)
1. **Domain: `https://ibid-irfani.id/`** (root domain). URL QR = `https://ibid-irfani.id/buku/{ibid}`. Dibaca dari config (`APP_URL`). Domain atas nama Penerbit Irfani + auto-renew multi-tahun (QR permanen).
2. **Format nomor IBID sesuai brief** `IRF-YYYY-NNNNNN`: tanpa segmen/offset, mulai 000001, NNNNNN naik berurutan global tanpa melihat tahun, YYYY = tahun terbit IBID (tahun saat Generate).
3. **Generator**: tabel counter 1 baris + `lockForUpdate` di `DB::transaction`, unique index sebagai jaring pengaman.
4. **Edit setelah IBID terbit**: hanya atribut; `ibid_number` immutable; riwayat before/after per field, tab "Riwayat" (admin-only).
5. **Tidak ada hapus permanen**; soft delete (karya, orang, kategori, pengajuan). Karya ber-IBID tidak boleh di-soft-delete, cukup diarsipkan/dinonaktifkan.
6. **Cover**: engine gambar dulu — re-encode GD, buang EXIF, batas ukuran/dimensi, nama random, thumbnail.
7. **QR**: SVG + PNG resolusi tinggi, ECC level H, tidak disimpan di disk, bisa diunduh kapan saja setelah ber-IBID, unduhan masuk audit log. Logo di tengah **opsional, ditunda** (lihat Fase 2b) — klien belum tentu mau pakai logo di QR-nya.
8. **head-assets**: buang Tailwind Play CDN; compile lokal pakai Tailwind standalone CLI (tanpa Node), `public/css/app.css` di-commit; Alpine.js (bila perlu) self-host. "No build" = tidak ada build di server.
9. **Reset password via email** + Ganti Password (SMTP domain, SPF/DKIM, token kadaluarsa, throttle, respons generik).
10. **Login sukses & gagal** masuk audit log.
11. **Deploy DB via import SQL dump dari lokal**; tidak ada seeder di hosting.
12. **Import CSV** disediakan (sub-fase 2b).
13. **IBID digenerate setelah pengajuan disetujui, sebelum editing/layout.**
14. **State machine dua sumbu**: `status_produksi` + `status_identitas` menggantikan `status` tunggal (migration baru; enum `StatusKarya` dipensiunkan).
15. **CSV import**: nama orang dianggap sudah rapi dari penerbit; singkatan = tanggung jawab penerbit; exact match ternormalisasi, tanpa fuzzy.
16. **Batalkan Penerbitan/IBID**: opsi tersedia bila penerbitan tidak jadi. IBID ikut batal, nomor hangus (tidak dipakai ulang).
17. **Halaman pra-terbit untuk promosi** (identitas = IBID Diterbitkan): tampil cover/placeholder, judul, subjudul, penulis, kategori, sinopsis, nomor IBID, badge "Terdaftar dalam Sistem IBID — Dalam Proses Penerbitan". Tidak tampil data pribadi dan data bibliografis belum final (tahun, edisi, kota, halaman, ukuran, ISBN). Tidak di `/cari`, `noindex`, ada Open Graph agar link enak dibagikan. (Menggantikan usulan awal "halaman minimal tanpa judul".)
18. **Syarat Dipublikasikan mencakup cover wajib.**
19. **Tampilan Tidak Aktif/Diarsipkan**: judul hanya tampil jika karya pernah dipublikasikan; selain itu halaman minimal "IBID ini dibatalkan / tidak berlaku".
20. **Switch `tampil_pra_terbit`** per karya (default aktif; jika dimatikan halaman pra-terbit hanya nomor IBID + badge). *Usulan Claude — menunggu OK Jo. Dipindah ke Fase 2b, bukan bagian Fase 2a.*

# State machine karya — dua sumbu

## Prinsip
- `status_produksi`, `status_identitas`, `ibid_number` TIDAK boleh diisi lewat form/mass-assignment (bukan `$fillable`). Status hanya lewat service transisi; nomor IBID hanya lewat `GenerateIbidAction`.
- Setiap transisi: DB transaction + lock baris karya, cek transisi legal + guard, audit log (aksi, before/after, alasan).
- Begitu karya punya IBID, `/buku/{ibid}` **tidak pernah 404**.
- Alasan wajib: mundur status produksi, Dibatalkan, Tidak Aktif, Diarsipkan.

## Sumbu 1 — Status Produksi
| Status | Arti |
|---|---|
| Draft | Karya diinput manual, belum lengkap / belum disetujui |
| Disetujui | Lolos verifikasi (hasil convert pengajuan, atau manual) — syarat Generate IBID |
| Dalam Proses | Editing naskah, layout, cetak (tracking poin 19 brief) |
| Diterbitkan | Buku selesai terbit/tercetak |
| Dibatalkan | Terminal: penerbitan batal sebelum terbit (IBID, bila ada, ikut batal) |

Draft → Disetujui → Dalam Proses → Diterbitkan (maju, manual); mundur satu langkah boleh dengan alasan. Draft/Disetujui/Dalam Proses → Dibatalkan (aksi Batalkan Penerbitan).

## Sumbu 2 — Status Identitas
| Status | Arti | Tampilan publik `/buku/{ibid}` |
|---|---|---|
| Belum Ber-IBID | `ibid_number` null | — |
| IBID Diterbitkan | Nomor digenerate, QR bisa dipakai | Halaman pra-terbit (promosi), lihat #17 |
| Dipublikasikan | Aktif penuh | Penuh + `/cari` + verifikasi |
| Tidak Aktif | Ditarik/dibatalkan, IBID tetap tercatat | Lihat #19 |
| Diarsipkan | Histori, hilang dari daftar aktif admin | Sama dengan Tidak Aktif |

Belum Ber-IBID → IBID Diterbitkan (Generate IBID, otomatis). IBID Diterbitkan → Dipublikasikan (manual). Dipublikasikan/IBID Diterbitkan → Tidak Aktif (alasan). Tidak Aktif → Dipublikasikan/IBID Diterbitkan (reaktivasi, tidak berlaku bila produksi Dibatalkan). Tidak Aktif ↔ Diarsipkan.

## Batalkan Penerbitan
Tersedia untuk produksi Draft/Disetujui/Dalam Proses. Atomik: produksi = Dibatalkan; bila sudah ber-IBID, identitas = Tidak Aktif. Alasan wajib + konfirmasi ketik ulang nomor IBID. Tidak bisa di-undo. QR yang sudah beredar tetap membuka halaman "IBID ini dibatalkan / tidak berlaku". Buku yang sudah Diterbitkan ditarik dari publik lewat Tidak Aktif (bukan Dibatalkan).

## Guard lintas sumbu
- **Generate IBID**: Belum Ber-IBID; produksi ∈ {Disetujui, Dalam Proses, Diterbitkan}; data minimum judul, kategori, ≥1 penulis.
- **Dipublikasikan**: produksi = Diterbitkan; data lengkap: tahun_terbit, kota_terbit, edisi, bahasa, jumlah_halaman, ukuran, **cover**.
- Ber-IBID → produksi ≠ Draft. Produksi tidak boleh mundur dari Diterbitkan selama Dipublikasikan.
- Soft delete hanya untuk Belum Ber-IBID.

## Invarian (jadi test)
I1 `ibid_number` null ⇔ Belum Ber-IBID · I2 ber-IBID ⇒ produksi ≠ Draft · I3 Dipublikasikan ⇒ produksi Diterbitkan + cover terisi · I4 `ibid_number` tidak pernah berubah/dipakai ulang · I5 Dibatalkan + ber-IBID ⇒ identitas Tidak Aktif/Diarsipkan.

## Titik masuk
Convert pengajuan Disetujui → produksi Disetujui, identitas Belum Ber-IBID (prefill). Input manual → Draft. Import CSV karya lama → produksi Draft/Diterbitkan (whitelist), identitas Belum Ber-IBID. Implementasi: enum + `allowedTransitions()` + service transisi, tanpa package tambahan.

# Fase 2b (lanjutan Fase 2, dibahas & dieksekusi lewat prompt terpisah)

## Import CSV
- Matching `orang`: exact match ternormalisasi (trim, spasi ganda, case-insensitive, Unicode normalize); cocok → pakai ulang, tidak cocok → baru. Preview "X orang baru, Y dipakai ulang" + daftar nama.
- Satu baris = satu karya; kolom penulis/editor/kontributor/penerjemah bisa banyak nama dipisah `|`. Template CSV diunduh dari aplikasi.
- Deteksi delimiter `,` vs `;`, UTF-8 (BOM ok), batas baris/ukuran per file.
- Alur: upload → validasi semua baris → preview + laporan error → konfirmasi → import transaksi all-or-nothing. File upload privat, dihapus setelah selesai, isi sel tidak dieksekusi.
- Bulk generate IBID: backlog.

## Switch `tampil_pra_terbit` (dipindah dari Fase 2a)
Keputusan #20. Kolom `tampil_pra_terbit` dan checkbox-nya di form Karya sudah ada sejak langkah 5 (default aktif), tapi halaman publik pra-terbit (`App\Http\Controllers\Publik\BukuController::halamanPraTerbit`) belum mengecek nilainya — selalu tampil versi lengkap. Masih menunggu OK/tolak Jo. Kalau OK: kalau `tampil_pra_terbit` false, halaman `/buku/{ibid}` untuk status IBID Diterbitkan cuma tampilkan nomor IBID + badge, tanpa cover/judul/subjudul/penulis/kategori/sinopsis.

## Logo QR (opsional, ditunda ke akhir)
Item #7. Logo Penerbit Irfani di tengah QR ditunda — klien belum tentu mau pakai logo di QR-nya. `App\Services\QrCodeService` sudah siap menampung: kalau ada berkas di `public/images/logo-qr.png`, otomatis dipakai; kalau tidak ada, QR tetap valid & scannable tanpa logo. Tidak perlu ubah kode untuk mengaktifkan, tinggal taruh berkasnya (atau putuskan tidak perlu logo sama sekali).

# Urutan task Fase 2
0. Perbaiki head-assets (Tailwind CLI, app.css committed, hapus CDN).
1. Migration tambahan + enum + model: softDeletes; `status` → `status_produksi` + `status_identitas`; `tampil_pra_terbit`, `tanggal_ibid`, `tanggal_dipublikasikan`; tabel `ibid_counter`; `keterangan` di audit_log.
2. Audit service (observer CRUD + aksi eksplisit) + viewer log + login sukses/gagal.
3. CRUD Kategori & Orang (soft delete; blokir hapus jika masih dipakai karya aktif).
4. Cover pipeline.
5. CRUD Karya (multi-role pivot, filter dua sumbu, paginasi, whitelist sort) + engine status + Batalkan Penerbitan + tab Riwayat + test invarian.
6. Generate IBID + QR (SVG/PNG) + `/buku/{ibid}` per status (pra-terbit, dipublikasikan, tidak aktif/dibatalkan) + verifikasi minimal.
7. Ganti Password + reset via email.
8. Dashboard statistik (breakdown per sumbu).

**Fase 2a (langkah 0-8) DITUTUP 2026-09-21 — commit `678c4c1`, sudah di-push ke `main`.**

9. (2b) Import CSV + switch `tampil_pra_terbit` + logo QR (opsional) — prompt terpisah, lihat bagian "Fase 2b" di atas.

# Catatan operasional
- Hosting tanpa SSH: migrate lewat import SQL dump dari lokal; dump ulang setiap ada migration baru.
- Antrian email: `sync`, atau cron `schedule:run` + `queue:work --stop-when-empty`.
- Jaga registrasi/perpanjangan domain `ibid-irfani.id`.
- `CLAUDE.md` di root repo masih menyebut "Tailwind CDN/CSS biasa (tanpa build step)" — bertentangan dengan keputusan #8; PRD §7 yang berlaku.

# Pertanyaan terbuka
- OK/tolak switch `tampil_pra_terbit` (#20) — sudah dipindah ke Fase 2b, tidak lagi menahan penutupan Fase 2a.
- Logo QR: dipakai atau tidak (klien belum tentu mau)? — Fase 2b, opsional.
- Panduan penempatan/ukuran minimum QR untuk editor: diserahkan ke klien, atau panduan singkat di halaman detail karya? (tidak blocking)

# Backlog / ide pengembangan
- Statistik scan QR (naik prioritas karena dipakai promosi).
- Bulk generate IBID dengan preview urutan.
- Riwayat perubahan publik (provenance).
- Export metadata CSV.
- Panduan penempatan QR untuk editor di halaman detail karya.
