---
title: IBID (Irfani Book Identity) — Product Requirements Document (PRD) Konsolidasi
status: living document — sumber kebenaran tunggal untuk development
tanggal: 2026-09-18
klien: Penerbit Irfani
---

# 1. Overview
IBID = Irfani Book Identity. Sistem identitas internal Penerbit Irfani untuk karya yang **tidak** pakai ISBN. **IBID bukan pengganti ISBN** — harus konsisten di semua copy & UI. Tagline: "Setiap Karya Memiliki Jejak". Karakter brand: profesional, modern, literer, akademik, sederhana, terpercaya. Kontak resmi: email `bukuirfani@gmail.com`, website `www.penerbitirfani.com`, IG `@penerbitirfani`.

# 2. Peran Pengguna
1. **Publik (tanpa login)** — cari karya, verifikasi IBID, lihat halaman identitas karya, ajukan naskah.
2. **Calon penulis/penulis** — mitra Penerbit Irfani, bukan pengguna internal. Tidak ada dashboard/akun. Satu-satunya jalur: form **Ajukan Penerbitan**.
3. **Superadmin** — satu-satunya pihak yang generate IBID & QR code, kelola semua data (karya, master data, pengajuan), verifikasi, audit trail. Untuk versi ini hanya ada level superadmin, tidak ada admin operator terpisah.

# 3. Ruang Lingkup
**Termasuk MVP**: beranda, cari & verifikasi IBID, halaman identitas karya, login & dashboard admin, CRUD data karya, generate IBID, generate QR code, alur pengajuan penerbitan (form + upload naskah + surat keaslian opsional), responsive design, link resmi Penerbit Irfani.

**Eksplisit di luar scope versi ini**: dashboard/akun penulis, registrasi mandiri, pengajuan/generate IBID oleh penulis, marketplace, pembayaran, e-commerce, level admin selain superadmin, 2FA.

**Tahap berikutnya (bukan MVP)**: statistik scan QR, katalog digital, profil penulis publik, export metadata, laporan, sertifikat digital, API pihak ketiga.

# 4. Alur Sistem
- **Publik**: Home -> Cari IBID -> Hasil/Verifikasi -> Halaman Identitas Karya.
- **Calon penulis**: Home -> Ajukan Penerbitan -> Form (identitas + naskah + karya) -> Upload Naskah (wajib) -> Upload Surat Keaslian (opsional) -> Kirim -> tersimpan sebagai `pengajuan` di aplikasi (bukan email) -> email konfirmasi ke pengaju.
  - Ajukan Penerbitan = pengajuan **penerbitan baru** (naskah mentah mau dicetak di Irfani). Karya yang sebelumnya sudah terbit (di Irfani/penerbit lain) tetap masuk jalur ini untuk "terbit ulang" dengan IBID baru.
- **Superadmin**: Login -> Dashboard -> Data Karya/Pengajuan -> Verifikasi -> (proses cetak, tracking) -> Generate IBID -> Generate QR Code -> Publikasikan Identitas.

# 5. Format Nomor IBID & Aturan Bisnis
- Format: `IRF-YYYY-NNNNNN`. Unik, otomatis, tidak bisa diduplikasi/diubah setelah diterbitkan.
- `NNNNNN` naik terus secara global (tidak reset tiap tahun); `YYYY` yang berubah.
- **Nomor digenerate baru saat superadmin klik "Generate IBID"** (bukan otomatis saat draft dibuat) — field nullable sampai titik itu.
- QR Code **statis**, mengarah ke URL halaman identitas (`/buku/{ibid}`), bukan menyimpan metadata langsung — supaya metadata bisa update tanpa ganti QR.
- Status **karya**: Draft -> Dalam Proses -> Disetujui -> Diterbitkan -> IBID Diterbitkan -> Dipublikasikan -> Tidak Aktif -> Diarsipkan. IBID yang sudah terbit **tidak boleh dihapus permanen** (soft-delete/arsip saja, histori tetap tersimpan).
- Status **pengajuan** (terpisah dari status karya): Baru -> Diproses -> Disetujui / **Ditolak**. "Ditolak" hanya ada di level pengajuan, bukan di level karya.

# 6. Data Model / Skema Database
- `users` — superadmin (nama, email, password hashed)
- `kategori` — master data kategori karya (CRUD oleh superadmin)
- `orang` — master data penulis/editor/kontributor/penerjemah: nama, nama pena, email, WA, alamat, kota, provinsi. Satu tabel gabungan (bukan terpisah per role).
- `karya_orang` — pivot: karya_id, orang_id, role (enum: penulis/editor/kontributor/penerjemah) — mendukung satu orang punya banyak role.
- `karya` — id, ibid_number (nullable, unique), isbn (nullable), judul, subjudul, kategori_id, tahun_terbit, kota_terbit, edisi, bahasa, jumlah_halaman, ukuran, sinopsis, kata_kunci, cover_path, status, tanggal_dibuat, tanggal_diterbitkan
- `pengajuan` — identitas pengaju (nama, nama pena, email, WA, alamat, kota, provinsi), judul, kategori, sinopsis, naskah_path, surat_keaslian_path (opsional), status (Baru/Diproses/Disetujui/Ditolak), catatan admin, karya_id (nullable — terisi kalau sudah di-convert jadi karya)
- `audit_log` — user_id, aksi, entitas, entitas_id, data_before/after (json), created_at, ip_address

Cover buku diupload manual oleh superadmin saat input Data Karya (bukan dari lampiran pengajuan). Field lain bisa di-prefill dari data `pengajuan`, sisanya dilengkapi manual.

# 7. Keputusan Teknis & Arsitektur
- Framework: **Laravel (versi terbaru) + Blade**. Admin panel **custom** (bukan Filament) supaya tema visual sama dengan halaman publik.
- Auth: **manual** (bukan Breeze/Fortify) — cuma 1 role (superadmin), tidak butuh scaffolding registrasi/reset password lengkap.
- Frontend/asset: **Tailwind via CDN atau CSS biasa, tanpa build step (no Vite)** — supaya deploy ke shared hosting cPanel tanpa SSH tinggal upload file, tidak perlu `npm run build` di server.
- QR Code: **`endroid/qr-code`** (pure-PHP, GD/Imagick, support custom logo/frame).
- Hosting: shared hosting cPanel biasa (bukan VPS/managed). Tidak ada akses SSH, tapi ada **Cron Jobs** — dipakai jalanin `artisan schedule:run` tiap menit.
- Storage **tanpa symlink**: cover buku di `public/storage-karya/` (akses publik langsung); naskah & surat keaslian di `storage/app/private/` (privat), diakses lewat route Laravel yang cek permission dulu baru file di-stream.
- Upload naskah/surat keaslian: **maksimal 10MB per file**, format **PDF/DOC/DOCX** saja.
- File privat diakses via identifier acak/hashed, bukan ID sequential yang gampang ditebak.

# 8. Desain UI
- Tema admin panel **disamakan** dengan halaman publik (hijau-cream), bukan tema admin generik.
- **Skema final**: warna Opsi B + font Opsi C.
  - Primary `#17352A` · Aksen emas `#B08D57` · Background `#F7F4EC` · Card `#FFFFFF` · Teks `#20241F` · Border `#E5E0CF`
  - Font: **Source Serif 4** (headline) + **Work Sans** (body)
- Preview skema warna/font tersimpan di project docs (claude.ai project "IBID Irfani"): `ibid-opsi-warna-font.html`.

# 9. Kebutuhan Keamanan (Security Requirements)
Security-first, wajib dicek di **setiap** fitur yang menyentuh input user, auth, file, atau query database — bukan cuma di akhir project. Rujuk skill `security-first-coding`.

**Autentikasi & sesi**
- Password di-hash (bcrypt/argon2 default Laravel), tidak pernah disimpan plaintext/dilog.
- Rate limiting / throttle di halaman login (cegah brute force).
- Session ID diregenerasi saat login; cookie session `secure` + `httponly` + `samesite`.
- Middleware auth wajib di semua route `/admin/*` kecuali `/admin/login`.

**Input & validasi**
- Semua input divalidasi eksplisit (Form Request class), pendekatan whitelist bukan blacklist.
- CSRF protection aktif di semua form (default Laravel `@csrf`, jangan pernah di-disable).
- Output di Blade pakai `{{ }}` (auto-escape); `{!! !!}` hanya kalau sudah tersanitasi dan benar-benar perlu — hindari sebisa mungkin.

**Upload file**
- Validasi ekstensi **dan** MIME type asli file (bukan cuma dari nama file) — PDF/DOC/DOCX, maks 10MB.
- File disimpan dengan nama baru random/hashed (bukan nama asli dari user) untuk cegah path traversal & collision.
- Naskah & surat keaslian disimpan di `storage/app/private/` (di luar public root), diakses lewat route yang cek permission per-request.
- Cover buku (publik) tetap divalidasi tipe file (image only) meski disimpan di `public/`.

**Database & query**
- Selalu lewat Eloquent ORM / query builder dengan parameter binding — tidak ada raw query dengan input user langsung disisipkan ke string SQL.
- Nomor IBID & ID sensitif lain di-generate/di-cek dengan mekanisme yang aman dari race condition (misal DB transaction/lock saat generate nomor urut).

**Akses & privasi data**
- Data pribadi penulis yang tidak perlu publik (email, WA, alamat lengkap) **tidak** ditampilkan di halaman identitas karya publik.
- Halaman verifikasi & pencarian publik diberi rate limiting untuk cegah scraping/abuse.
- URL file privat tidak boleh predictable/sequential.

**Infrastruktur**
- `APP_DEBUG=false` di production — jangan expose stack trace/debug info ke publik. Custom error page untuk 404/500.
- `.env` tidak pernah masuk git/ter-commit; kredensial tidak pernah hardcoded di kode.
- HTTPS/SSL wajib di production, force redirect HTTP -> HTTPS.
- Backup database berkala (via fitur backup cPanel/cron).
- Audit trail wajib jalan untuk semua aksi mutating di admin (create/update/delete/generate IBID/generate QR/approve/reject pengajuan).

**Di luar scope keamanan otomatis**
- Testing perilaku/UX end-to-end (smoke test) dilakukan manual oleh Jo — bukan tanggung jawab proses coding otomatis. Fokus coding tetap ke kebenaran teknis + keamanan, bukan bikin automated UI test kecuali diminta eksplisit.

# 10. Halaman & Route (draft)
**Publik**
- `GET /` — Beranda
- `GET /cari` — Cari IBID (nomor/judul/penulis/ISBN)
- `GET /verifikasi` — Verifikasi nomor IBID
- `GET /buku/{ibid}` — Halaman identitas karya
- `GET|POST /ajukan-penerbitan` — Form pengajuan penerbitan
- `GET /tentang` — Tentang IBID (FAQ)

**Admin** (prefix `/admin`, middleware `auth`)
- `GET|POST /admin/login`, `POST /admin/logout`
- `GET /admin/dashboard` — statistik ringkas
- `/admin/karya` — CRUD Data Karya, generate IBID, generate QR
- `/admin/pengajuan` — daftar & proses pengajuan masuk (approve/reject -> convert ke karya)
- `/admin/orang` — master data penulis/editor/kontributor/penerjemah
- `/admin/kategori` — master data kategori
- `/admin/laporan` — laporan (tahap lanjutan, boleh placeholder di Fase 1)
- `/admin/pengaturan` — pengaturan dasar (opsional Fase 1)

# 11. Definition of Done — Fase 1
- Project Laravel jalan lokal, koneksi DB OK.
- Semua migration di §6 jalan tanpa error, model + relasi (termasuk multi-role `orang`<->`karya`) berfungsi lewat tinker/test manual.
- Login superadmin manual berfungsi, route `/admin/*` ke-protect middleware auth, ada rate limiting di login.
- Base layout Blade publik & admin pakai token warna/font final, responsive dasar (mobile-friendly).
- Struktur storage jalan: cover ke `public/storage-karya/`, naskah/surat ke `storage/app/private/`, route stream privat sudah cek permission.
- Seeder superadmin default + beberapa kategori contoh berhasil jalan.
- Checklist keamanan dasar di §9 (yang relevan di tahap ini: hashing password, CSRF aktif, `.env` di `.gitignore`, `APP_DEBUG` sesuai environment) sudah terpasang sejak awal, bukan ditambah belakangan.

# 12. Pertanyaan Terbuka
Tidak ada pertanyaan blocking tersisa untuk mulai Fase 1. (Batasan upload sudah diputuskan: 10MB, PDF/DOC/DOCX.)
