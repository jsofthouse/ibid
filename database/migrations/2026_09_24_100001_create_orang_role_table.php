<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Role yang di-declare pada master data Orang (kemampuan umum). Berbeda
     * dari karya_orang.role yang merupakan assignment aktual per karya.
     */
    public function up(): void
    {
        Schema::create('orang_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orang_id')->constrained('orang')->cascadeOnDelete();
            $table->enum('role', ['penulis', 'editor', 'kontributor', 'penerjemah']);

            $table->unique(['orang_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_role');
    }
};
