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
        Schema::create('alumno_tecnologia', function (Blueprint $table) {
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_tecnologia');

            // Atributos extra de la relación
            $table->enum('tipo_relacion', ['CONOCE', 'INTERES']);
            $table->integer('nivel')->default(0); // Del 1 al 10, por ejemplo

            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos')->onDelete('cascade');
            $table->foreign('id_tecnologia')->references('id_tecnologia')->on('tecnologias')->onDelete('cascade');

            // La PK incluye el tipo para que un alumno pueda tener la misma tecno como CONOCE y como INTERES si se quisiera
            $table->primary(['id_alumno', 'id_tecnologia', 'tipo_relacion']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno_tecnologia');
    }
};
