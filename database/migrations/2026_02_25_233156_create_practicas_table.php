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
        Schema::create('practicas', function (Blueprint $table) {
            $table->id('id_practica');

            $table->foreignId('id_oferta')->constrained('ofertas', 'id_oferta')->onDelete('cascade');
            $table->foreignId('id_alumno')->constrained('alumnos', 'id_alumno')->onDelete('cascade');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['SOLICITADA', 'EN_CURSO', 'FINALIZADA', 'CANCELADA'])->default('SOLICITADA');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practicas');
    }
};
