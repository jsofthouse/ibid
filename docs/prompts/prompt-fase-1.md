# Prompt Kickoff — Fase 1 (siap paste ke Claude Code)

Baca dulu CLAUDE.md di root project ini, docs/PRD.md, dan docs/rencana-fase-1.md sebelum mulai. Ikuti semua aturan di CLAUDE.md (security-first tanpa kecuali, batasan file hanya yang relevan, tanya dulu kalau ragu, komunikasi Bahasa Indonesia + skill caveman mode ultra, kecuali untuk hal kritis/security tetap harus jelas).

Tugas: eksekusi Fase 1 (Fondasi) untuk project IBID sesuai docs/rencana-fase-1.md, urut per langkah, konfirmasi ke saya di titik-titik penting sebelum lanjut ke langkah berikutnya kalau ada keputusan yang belum eksplisit di dokumen:

1. Setup project Laravel baru (versi terbaru) di folder ini. Init git, siapkan .env, sambungkan ke database lokal (MySQL via Laragon). Pastikan .env masuk .gitignore.
2. Buat migration untuk 7 tabel di docs/PRD.md §6: users, kategori, orang, karya, karya_orang (pivot), pengajuan, audit_log. Field sesuai spec di dokumen itu.
3. Buat Eloquent model + relasi untuk semua tabel di atas, termasuk relasi multi-role orang <-> karya lewat pivot karya_orang.
4. Bangun auth superadmin manual (bukan Breeze) — form login, middleware proteksi /admin/*, rate limiting di endpoint login, password hashed.
5. Buat base layout Blade (publik & admin) pakai Tailwind CDN/CSS biasa (tanpa build step), terapkan token warna & font final dari docs/PRD.md §8.
6. Setup struktur storage: cover buku ke public/storage-karya/, naskah & surat keaslian ke storage/app/private/ (bukan public), buat route stream file privat yang cek auth+permission sebelum serve file.
7. Buat seeder: 1 akun superadmin default (pakai kredensial dummy/env, jangan hardcode password di kode) + beberapa kategori contoh.

Terapkan checklist keamanan docs/PRD.md §9 sejak langkah pertama (bukan ditambah belakangan) — terutama: CSRF aktif di semua form, APP_DEBUG=false untuk production env, validasi upload (ekstensi+MIME+ukuran 10MB), parameter binding di semua query, tidak ada raw SQL dari input user.

Kalau ada bagian yang butuh keputusan di luar yang sudah tertulis di docs/PRD.md atau docs/rencana-fase-1.md, atau ada request yang berpotensi melanggar aturan CLAUDE.md — berhenti dan tanya dulu ke saya, jangan lanjut eksekusi sendiri.
