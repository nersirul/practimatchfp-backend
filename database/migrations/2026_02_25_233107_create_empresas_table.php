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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id('id_empresa');
            $table->string('nombre_comercial');
            $table->string('cif')->unique();
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->integer('num_trabajadores')->nullable();
            $table->string('sector')->nullable();
            $table->string('email_contacto')->unique();
            $table->string('password');
            $table->text('descripcion')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
