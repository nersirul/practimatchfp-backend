<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id('id_valoracion');

            // Relacion 1:1, una práctica tiene una sola valoración final
            $table->foreignId('id_practica')->unique()->constrained('practicas', 'id_practica')->onDelete('cascade');

            // NUEVOS CAMPOS FCT (Profesor)
            $table->enum('calificacion', ['APTO', 'NO APTO']);
            $table->integer('nota_numerica')->nullable(); // Nota del 1 al 10
            $table->text('comentarios_profesor')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
