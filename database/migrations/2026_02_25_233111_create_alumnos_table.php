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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id('id_alumno');
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('nif')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('ciclo')->nullable();
            // Usamos enum para restringir valores
            $table->enum('modalidad_preferida', ['REMOTO', 'PRESENCIAL', 'HIBRIDO'])->default('PRESENCIAL');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
