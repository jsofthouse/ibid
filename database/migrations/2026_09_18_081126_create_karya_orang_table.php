<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karya_orang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karya_id')->constrained('karya')->cascadeOnDelete();
            $table->foreignId('orang_id')->constrained('orang')->cascadeOnDelete();
            $table->enum('role', ['penulis', 'editor', 'kontributor', 'penerjemah']);
            $table->timestamps();

            $table->unique(['karya_id', 'orang_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karya_orang');
    }
};
