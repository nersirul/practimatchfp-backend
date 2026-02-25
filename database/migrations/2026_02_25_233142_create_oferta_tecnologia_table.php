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
        Schema::create('oferta_tecnologia', function (Blueprint $table) {
            $table->unsignedBigInteger('id_oferta');
            $table->unsignedBigInteger('id_tecnologia');

            $table->foreign('id_oferta')->references('id_oferta')->on('ofertas')->onDelete('cascade');
            $table->foreign('id_tecnologia')->references('id_tecnologia')->on('tecnologias')->onDelete('cascade');

            $table->primary(['id_oferta', 'id_tecnologia']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta_tecnologia');
    }
};
