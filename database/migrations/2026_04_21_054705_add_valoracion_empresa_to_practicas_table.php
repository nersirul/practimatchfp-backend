<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practicas', function (Blueprint $table) {
            $table->integer('puntuacion_empresa')->nullable()->after('estado');
            $table->text('comentario_alumno')->nullable()->after('puntuacion_empresa');
        });
    }

    public function down(): void
    {
        Schema::table('practicas', function (Blueprint $table) {
            $table->dropColumn(['puntuacion_empresa', 'comentario_alumno']);
        });
    }
};
