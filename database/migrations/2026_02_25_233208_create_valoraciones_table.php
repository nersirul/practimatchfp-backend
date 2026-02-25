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
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id('id_valoracion');

            // Relacion 1:1, una práctica tiene una sola valoración final
            $table->foreignId('id_practica')->unique()->constrained('practicas', 'id_practica')->onDelete('cascade');

            $table->tinyInteger('puntuacion')->unsigned(); // Entero pequeño (1-5)
            $table->text('comentario')->nullable();
            $table->dateTime('fecha_registro')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
