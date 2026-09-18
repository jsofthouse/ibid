# CLAUDE.md — Aturan Kerja untuk Claude Code di Project IBID

Project: IBID (Irfani Book Identity) — sistem identitas buku untuk Penerbit Irfani.
Stack: Laravel (versi terbaru) + Blade, auth manual, Tailwind CDN/CSS biasa (tanpa build step), storage tanpa symlink, endroid/qr-code.

**WAJIB baca dulu sebelum kerja apapun**: `docs/PRD.md` (spec lengkap, skema database, security requirements, route list) dan `docs/rencana-fase-1.md` (breakdown task Fase 1). Semua keputusan teknis & bisnis ada di situ — jangan menebak/mengasumsikan hal yang sudah didefinisikan di sana.

## Aturan Wajib (Tidak Bisa Ditawar)

1. **Security-first, tanpa kecuali.** Rujuk skill `security-first-coding` di SETIAP fitur/perubahan yang menyentuh input user, auth, file/upload, atau query database — bukan cuma dicek di akhir. Checklist keamanan lengkap ada di `docs/PRD.md` §9 (auth & sesi, validasi input, upload file, query database, akses & privasi data, infrastruktur). Tidak ada satupun poin di checklist itu yang boleh dilewatkan atau ditunda "nanti aja".

2. **Batasan file — hanya sentuh yang relevan dengan task berjalan.** Jangan buat, ubah, atau hapus file apapun di luar konteks task yang sedang dikerjakan. Jangan sentuh file konfigurasi sistem, folder lain di luar project ini, atau bagian project yang tidak diminta. Kalau suatu task butuh menyentuh file di luar scope yang jelas, itu masuk kategori "ragu" di poin 3 — berhenti dan tanya dulu.

3. **Kalau ragu atau ada potensi melanggar rules di atas, STOP dan tanya dulu ke user** sebelum eksekusi. Jangan asumsi, jangan jalan duluan baru minta maaf belakangan. Ini termasuk: keputusan yang belum ada di `docs/PRD.md`, konflik antara request dengan aturan security, atau task yang ambigu scope-nya.

4. **Behavior/UX testing bukan tanggung jawab di sini.** Smoke test manual dilakukan sendiri oleh user (Jo). Fokus kerja: kebenaran teknis dan keamanan kode. Tidak perlu bikin automated UI/browser test kecuali diminta eksplisit.

## Gaya Komunikasi

- Selalu komunikasi dalam **Bahasa Indonesia**.
- Gunakan skill **caveman (mode ultra)** untuk komunikasi ke user — ringkas, hemat token.
- Pengecualian: kalau lagi menjelaskan risiko keamanan, keputusan yang butuh persetujuan user, atau alasan kenapa berhenti/bertanya (poin 3 di atas) — tetap ringkas tapi JANGAN sampai informasi kritis jadi hilang atau ambigu gara-gara gaya compressed. Kejelasan di hal-hal penting menang di atas keringkasan.

## Referensi Cepat
- Skema database, format nomor IBID, route list, definition of done Fase 1 → `docs/PRD.md`
- Breakdown task & urutan kerja Fase 1 → `docs/rencana-fase-1.md`
