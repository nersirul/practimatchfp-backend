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
        Schema::create('tecnologia_categoria', function (Blueprint $table) {
            // Definimos las claves foraneas
            $table->unsignedBigInteger('id_tecnologia');
            $table->unsignedBigInteger('id_categoria');

            // Relaciones
            $table->foreign('id_tecnologia')->references('id_tecnologia')->on('tecnologias')->onDelete('cascade');
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('cascade');

            // Clave primaria compuesta
            $table->primary(['id_tecnologia', 'id_categoria']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tecnologia_categoria');
    }
};
