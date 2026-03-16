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
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('telefono')->nullable()->after('email');
            $table->string('direccion')->nullable()->after('telefono');
            $table->string('ciudad')->nullable()->after('direccion');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->string('telefono_contacto')->nullable()->after('email_contacto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'direccion', 'ciudad']);
        });
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('telefono_contacto');
        });
    }
};
