<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA BLOQUE DE VERSICULO POR CADA BLOQUE DE CAPITULO
     */
    public function up(): void
    {
        Schema::create('biblia_versiculo_bloque', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_biblia_capitulo_bloque')->unsigned();

            $table->integer('numero');
            $table->boolean('visible');

            $table->foreign('id_biblia_capitulo_bloque')->references('id')->on('biblia_capitulo_bloque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblia_versiculo_bloque');
    }
};
