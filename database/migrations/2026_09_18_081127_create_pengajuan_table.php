<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_pena')->nullable();
            $table->string('email');
            $table->string('nomor_wa')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('judul');
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->text('sinopsis')->nullable();
            $table->string('naskah_path');
            $table->string('surat_keaslian_path')->nullable();
            $table->string('status')->default('baru');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('karya_id')->nullable()->constrained('karya')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};
