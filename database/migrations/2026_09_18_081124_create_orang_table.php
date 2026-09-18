<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orang', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_pena')->nullable();
            $table->string('email')->nullable();
            $table->string('nomor_wa')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang');
    }
};
