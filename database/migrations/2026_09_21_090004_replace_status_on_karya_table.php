<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karya', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('karya', function (Blueprint $table) {
            $table->string('status_produksi')->default('draft')->after('cover_path');
            $table->string('status_identitas')->default('belum_ber_ibid')->after('status_produksi');
            $table->boolean('tampil_pra_terbit')->default(true)->after('status_identitas');
            $table->date('tanggal_ibid')->nullable()->after('tanggal_diterbitkan');
            $table->date('tanggal_dipublikasikan')->nullable()->after('tanggal_ibid');

            $table->index(['status_produksi', 'status_identitas']);
        });
    }

    public function down(): void
    {
        Schema::table('karya', function (Blueprint $table) {
            $table->dropIndex(['status_produksi', 'status_identitas']);
            $table->dropColumn([
                'status_produksi', 'status_identitas', 'tampil_pra_terbit',
                'tanggal_ibid', 'tanggal_dipublikasikan',
            ]);
        });

        Schema::table('karya', function (Blueprint $table) {
            $table->string('status')->default('draft');
        });
    }
};
