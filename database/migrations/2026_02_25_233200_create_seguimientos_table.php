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
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id('id_seguimiento');

            $table->foreignId('id_practica')->constrained('practicas', 'id_practica')->onDelete('cascade');

            $table->dateTime('fecha_envio')->useCurrent();
            $table->enum('tipo', ['ENCUESTA_ALUMNO', 'ENCUESTA_TUTOR'])->default('ENCUESTA_ALUMNO');
            $table->json('respuestas_json')->nullable(); // Guardamos las respuestas en JSON flexible
            $table->boolean('completado')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};
