<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Isi orang_role dari role unik yang pernah dimiliki tiap orang di
     * karya_orang. Idempotent: unique(orang_id, role) + insert-or-ignore,
     * jadi aman dijalankan ulang tanpa duplikasi.
     */
    public function up(): void
    {
        DB::table('orang_role')->insertOrIgnoreUsing(
            ['orang_id', 'role'],
            DB::table('karya_orang')->select('orang_id', 'role')->distinct(),
        );
    }

    public function down(): void
    {
        // Data-only backfill: tabel orang_role di-drop oleh migration pembuatnya.
    }
};
