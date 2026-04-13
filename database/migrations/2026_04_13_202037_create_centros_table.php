<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('centros', function (Blueprint $table) {
            $table->id('id_centro');
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Añadir claves foráneas a alumnos y profesores
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('id_centro')->nullable()->constrained('centros', 'id_centro');
            $table->foreignId('id_profesor')->nullable()->constrained('profesores', 'id_profesor')->nullOnDelete();
        });

        Schema::table('profesores', function (Blueprint $table) {
            $table->foreignId('id_centro')->nullable()->constrained('centros', 'id_centro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros');
    }
};
