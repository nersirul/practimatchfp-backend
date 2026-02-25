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
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id('id_oferta');

            // FK Empresa (Si se borra empresa, se borran ofertas)
            $table->foreignId('id_empresa')->constrained('empresas', 'id_empresa')->onDelete('cascade');

            // FK Admin (Puede ser nulo si nadie la ha validado aun)
            $table->foreignId('id_admin_validador')->nullable()->constrained('administradores', 'id_admin')->onDelete('set null');

            $table->string('titulo');
            $table->text('descripcion');
            $table->enum('modalidad', ['REMOTO', 'PRESENCIAL', 'HIBRIDO']);
            $table->boolean('es_remunerada')->default(false);
            $table->boolean('posibilidad_contratacion')->default(false);
            $table->enum('estado', ['PENDIENTE', 'PUBLICADA', 'CERRADA'])->default('PENDIENTE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
