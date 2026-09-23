---
title: IBID — Rencana Fase 3 (Sisi Publik + Manajemen Pengajuan Admin)
status: breakdown disepakati, siap eksekusi — jalan per langkah dengan konfirmasi Jo di tiap langkah
tanggal: 2026-09-23
---

# Konteks

Fase 1 (fondasi) dan Fase 2a (Admin Panel Core) sudah selesai & di-push ke `main` (162/162 test hijau, Pint bersih — commit `678c4c1`, ditutup lewat `ca67bbb`). Fase 2b (import CSV, logo QR opsional) sengaja ditunda, **jangan dikerjakan di Fase 3 ini**.

Fase 3 = sisi publik (beranda, cari, verifikasi, ajukan penerbitan, tentang) + satu prasyarat admin yang ketinggalan dari Fase 2a: manajemen pengajuan (`/admin/pengajuan`). PRD (`docs/PRD.md`) tetap sumber kebenaran utama; dokumen ini = urutan kerja + detail eksekusi untuk Fase 3.

## Verifikasi kondisi kode per 2026-09-23 (sebelum mulai)
- **Belum ada** route/controller untuk `/admin/pengajuan` di `routes/web.php` maupun `app/Http/Controllers/Admin/`.
- **Sudah ada** dari Fase 1/2: model [`app/Models/Pengajuan.php`](../app/Models/Pengajuan.php), enum [`app/Enums/StatusPengajuan.php`](../app/Enums/StatusPengajuan.php), migration tabel `pengajuan` (termasuk soft delete). Jadi Langkah 0 tinggal bangun layer controller/view/service-nya, skema data sudah siap.
- Route stream file privat (naskah/surat keaslian) sudah ada dari Fase 1 (`FileStreamController`) — dipakai ulang di Langkah 0 untuk link unduh di halaman detail pengajuan.

# Lingkup & batasan

- Satu langkah = satu commit, pesan jelas Bahasa Indonesia.
- Tiap langkah: kerjakan → jalankan test logika relevan → ringkas hasil → **stop, tunggu konfirmasi Jo** sebelum lanjut langkah berikutnya.
- Checklist keamanan `docs/PRD.md` §9 diterapkan di setiap langkah yang relevan, bukan ditunda ke akhir. Rujuk skill `security-first-coding` di tiap fitur yang menyentuh input user, auth, file, atau query.
- Tidak menyentuh file di luar konteks langkah yang sedang jalan.
- Fase 2b (import CSV, logo QR) tetap di luar scope — jangan dikerjakan.

# Urutan task Fase 3

## Langkah 0 — Admin: Manajemen Pengajuan (`/admin/pengajuan`)
- Resource controller: `index` dengan filter status + paginasi + whitelist kolom sort; `show` detail pengajuan dengan link unduh naskah/surat keaslian lewat route stream privat yang sudah ada (permission check tetap jalan).
- Aksi ubah status:
  - Baru → Diproses → Disetujui: **convert jadi `Karya` baru** — `status_produksi = Disetujui`, `status_identitas = Belum Ber-IBID`, prefill judul/kategori/sinopsis/penulis dari data pengajuan, isi `karya_id` di baris pengajuan.
  - → Ditolak: catatan admin **wajib** (Form Request khusus).
- Guard eksplisit: pengajuan yang sudah punya `karya_id` (sudah di-convert) **tidak boleh** di-convert ulang.
- Audit log untuk tiap aksi (approve/reject/convert) — pola sama seperti observer/service Fase 2.
- Test: transisi status, hasil convert-to-karya benar (field ter-prefill, status awal benar), guard re-convert, validasi catatan wajib saat reject.

## Langkah 1 — Quick fix `tampil_pra_terbit`
- Di `Publik\BukuController::halamanPraTerbit()`: kalau `$karya->tampil_pra_terbit === false`, kirim view minimal (nomor IBID + badge saja — tanpa judul/cover/sinopsis/penulis), dan jangan bocor lewat meta Open Graph.
- Kalau `true` (default): perilaku tetap seperti sekarang (versi lengkap pra-terbit).
- Test: kedua varian (true/false).
- Catatan: ini item yang sudah menunggu OK Jo dari Fase 2 (PRD §12, rencana-fase-2.md keputusan #20) — dieksekusi di sini karena keputusan sudah final (dipindah ke Fase 2b lalu ditarik ke awal Fase 3 sesuai arahan Jo).

## Langkah 2 — Beranda publik
**REPLIKASI DESAIN REFERENSI, BUKAN REDESAIN.** Referensi: project file "sampel tampilan 3.jpeg" (desain final dari klien, Penerbit Irfani). Ganti `resources/views/public/home.blade.php` dari placeholder jadi persis sesuai referensi, elemen per elemen, urutan atas ke bawah:

1. Header: nav "Beranda | Cari IBID | Tentang | Kontak" (Beranda underline aktif), ikon search, tombol "Masuk" (solid) + "Daftarkan Karya" (outline).
2. Hero: label kecil "IRFANI BOOK IDENTITY", headline serif dua baris "Setiap Karya Memiliki Jejak", garis pemisah pendek, paragraf deskripsi, search bar besar (placeholder "Cari nomor IBID, judul buku, atau nama penulis...") + tombol "Cari", link "Lihat semua karya →". Pojok kanan atas: teks kecil rata-kanan "KARYA / MENEMUKAN / JALANNYA" + garis kecil.
3. 3 fitur (Identitas Unik, Mudah Diverifikasi, Untuk Masa Depan) — ikon outline dalam lingkaran, dipisah garis vertikal tipis.
4. Bar statistik (Karya Terdaftar, Penulis, Kategori, "Sejak 2024 Bersama Literasi") dipisah garis vertikal — angka dari query agregat real ke database, bukan hardcode, format ribuan ala Indonesia.
5. Quote section: kutipan italic serif + atribusi "PENERBIT IRFANI", teks kecil rata-kanan "BUKU / LEBIH DARI / SEKADAR KERTAS".
6. Footer: deskripsi singkat, kolom Tautan, kolom Bantuan, kolom Ikuti Kami (ikon sosial), copyright.

Aturan ketat:
- Jangan menambah section/elemen/dekorasi yang tidak ada di referensi (tidak ada gradient/shadow/animasi tambahan, tidak ada copy baru tanpa sumber).
- Pakai token warna/font final dari Fase 2a (`#17352A`, `#B08D57`, `#F7F4EC`, `#FFFFFF`, `#20241F`, `#E5E0CF`, Source Serif 4 + Work Sans) — tidak improvisasi warna/font baru.
- Elemen yang pemetaan route-nya belum jelas → **stop, tanya Jo dulu**, jangan menebak:
  - Link nav "Kontak" — ke mana?
  - Tombol "Masuk" — ke `/admin/login`?
  - Tombol "Daftarkan Karya" — ke `/ajukan-penerbitan`?
  - Search bar hero — submit ke `/cari?q=...` atau sekadar visual (belum fungsional sebelum Langkah 3 selesai)?
- Hal yang menurut Claude penting untuk di-improve (kontras warna, ukuran tap-target, aksesibilitas, dll) — **laporkan ke ringkasan langkah ini, jangan langsung diubah dari referensi.** Jo yang putuskan.
- Test: statistik yang ditampilkan cocok dengan hasil query agregat sebenarnya (bukan angka fixed).

## Langkah 3 — `GET /cari`
- Pencarian nomor IBID / judul / nama penulis (termasuk nama pena) / ISBN — Eloquent/query builder dengan parameter binding, whitelist field pencarian & sort.
- Hanya karya `status_identitas = Dipublikasikan` yang muncul.
- Rate limiting (throttle) di route.
- Test: karya non-Dipublikasikan tidak pernah muncul di hasil.

## Langkah 4 — `GET /verifikasi`
- Input nomor IBID → tampilkan status:
  - Dipublikasikan: valid + link ke `/buku/{ibid}`.
  - IBID Diterbitkan: "Dalam Proses Penerbitan".
  - Tidak Aktif / Diarsipkan / nomor tidak ada: "tidak ditemukan/tidak berlaku".
- Rate limiting sama seperti Langkah 3.

## Langkah 5 — `GET|POST /ajukan-penerbitan`
- Form Request khusus, whitelist field: identitas pengaju, judul, kategori, sinopsis, upload naskah (wajib), upload surat keaslian (opsional).
- Validasi file: ekstensi **dan** MIME asli, PDF/DOC/DOCX, maks 10MB, nama file random/hashed, simpan di `storage/app/private/` (pola sama Fase 1).
- Simpan sebagai `Pengajuan` status Baru — pastikan tidak ada mass-assignment ke field yang seharusnya tidak diisi publik (`status`, `karya_id`).
- Kirim email konfirmasi ke pengaju (mailer sesuai `.env`, jangan hardcode kredensial).
- CSRF aktif, rate limiting submit.
- Test: validasi file, field wajib vs opsional, email terkirim (fake mailer), data tersimpan benar, tidak ada mass-assignment ke kolom terlarang.

## Langkah 6 — `GET /tentang`
- Halaman statis FAQ: IBID vs ISBN, cara kerja, kontak resmi Penerbit Irfani (email `bukuirfani@gmail.com`, `www.penerbitirfani.com`, IG `@penerbitirfani`) sesuai brief §1.

# Checklist keamanan per langkah (rujuk PRD §9)
- Rate limiting di semua endpoint publik baru (cari, verifikasi, ajukan-penerbitan).
- Whitelist field pencarian/sort (Langkah 0 admin, Langkah 3 publik).
- Validasi MIME asli (bukan cuma ekstensi/nama file) untuk upload naskah/surat keaslian.
- Data pribadi pengaju/penulis tidak pernah tampil di halaman publik manapun (beranda, cari, verifikasi, buku/{ibid}).
- Tidak ada mass-assignment ke kolom terlarang (`status`, `karya_id` di pengajuan; `status_produksi`, `status_identitas`, `ibid_number` di karya).
- Form Request untuk semua input publik & admin baru; CSRF aktif di semua form.

# Pertanyaan terbuka (blocking sebelum langkah terkait)
- Langkah 2: pemetaan link nav "Kontak", tombol "Masuk", tombol "Daftarkan Karya", dan perilaku search bar hero (submit ke `/cari?q=...` atau visual saja) — tanya Jo sebelum eksekusi.
- Langkah 2: lokasi/akses file referensi "sampel tampilan 3.jpeg" perlu dipastikan sebelum mulai (project file claude.ai, bukan di filesystem lokal per pengecekan awal) — konfirmasi ke Jo cara aksesnya.
