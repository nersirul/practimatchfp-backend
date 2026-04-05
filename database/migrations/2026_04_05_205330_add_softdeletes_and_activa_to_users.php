<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadimos campo 'activa' y softDeletes a Empresas
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('activa')->default(false)->after('cif'); // Por defecto nacen inactivas
            $table->softDeletes();
        });

        // 2. Añadimos softDeletes al resto de usuarios
        Schema::table('alumnos', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('profesores', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('administradores', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['activa', 'deleted_at']);
        });
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('profesores', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('administradores', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
