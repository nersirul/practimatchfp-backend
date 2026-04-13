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
        Schema::table('ofertas', function (Blueprint $table) {
            // vacantes puede ser nulo (ilimitado)
            $table->integer('vacantes')->nullable()->after('estado');
            // activa nos permite pausar la oferta sin borrarla
            $table->boolean('activa')->default(true)->after('vacantes');
        });
    }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropColumn(['vacantes', 'activa']);
        });
    }
};
