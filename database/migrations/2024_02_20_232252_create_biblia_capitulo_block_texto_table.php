<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LOS NOMBRES DE CADA BLOQUE DE EL CAPITULO, MAYORIA SERAN NUMEROS
     */
    public function up(): void
    {
        Schema::create('biblia_capitulo_block_texto', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_biblia_capitulo_block')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            // mayormente seran numeros
            $table->string('titulo', 50);

            $table->foreign('id_biblia_capitulo_block')->references('id')->on('biblia_capitulo_bloque');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblia_capitulo_block_texto');
    }
};
