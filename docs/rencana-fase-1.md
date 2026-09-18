---
title: IBID — Rencana & Keputusan Teknis Fase 1 (Fondasi)
status: breakdown disepakati, siap eksekusi
tanggal: 2026-09-18
---

# Fase 1 — Fondasi

Tujuan Fase 1: siapin fondasi teknis project sebelum masuk fitur (Fase 2: Admin Panel Core).

## Keputusan teknis yang dikunci

1. Skema master data "Orang": satu tabel `orang` + pivot `karya_orang` (role per karya). Satu orang bisa punya banyak role.
2. Timing generate nomor IBID: field `ibid_number` nullable, baru diisi saat superadmin klik "Generate IBID".
3. Library QR Code: `endroid/qr-code`.
4. Auth & asset pipeline: auth superadmin manual (bukan Breeze), Tailwind CDN/CSS biasa (tanpa build step Vite).
5. Status "Ditolak" melekat di tabel `pengajuan`, bukan di tabel `karya`.
6. Batasan upload naskah/surat keaslian: maksimal 10MB, format PDF/DOC/DOCX.

## Task breakdown eksekusi Fase 1 (urutan kerja)

1. Setup project Laravel baru (composer create-project, .env, koneksi DB, git init)
2. Buat migration untuk semua tabel (lihat docs/PRD.md §6): users, kategori, orang, karya, karya_orang, pengajuan, audit_log
3. Buat model + relasi (termasuk relasi multi-role orang <-> karya)
4. Auth superadmin manual (login form, middleware, rate limiting)
5. Base layout Blade (partial header/footer/sidebar admin) + terapkan token warna/font final
6. Struktur storage: cover di public/storage-karya/, naskah & surat keaslian di storage/app/private/, route Laravel buat stream file private dengan cek permission
7. Seeder awal (superadmin default, beberapa kategori contoh)

Detail lengkap spec, skema field, dan security requirements: lihat docs/PRD.md.
