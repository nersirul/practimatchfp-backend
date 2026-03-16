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
        Schema::table('practicas', function (Blueprint $table) {
            // Añadimos la clave foránea del profesor
            $table->foreignId('id_profesor')->nullable()->after('id_alumno')->constrained('profesores', 'id_profesor')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practicas', function (Blueprint $table) {
            $table->dropForeign(['id_profesor']);
            $table->dropColumn('id_profesor');
        });
    }
};
