<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karya', function (Blueprint $table) {
            $table->id();
            $table->string('ibid_number')->nullable()->unique();
            $table->string('isbn')->nullable();
            $table->string('judul');
            $table->string('subjudul')->nullable();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->unsignedSmallInteger('tahun_terbit')->nullable();
            $table->string('kota_terbit')->nullable();
            $table->string('edisi')->nullable();
            $table->string('bahasa')->nullable();
            $table->unsignedInteger('jumlah_halaman')->nullable();
            $table->string('ukuran')->nullable();
            $table->text('sinopsis')->nullable();
            $table->text('kata_kunci')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('status')->default('draft');
            $table->date('tanggal_dibuat')->nullable();
            $table->date('tanggal_diterbitkan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karya');
    }
};
