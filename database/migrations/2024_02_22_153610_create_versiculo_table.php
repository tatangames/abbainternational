<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * POR CADA CAPITULO SE LLENA LISTADO DE VERSICULOS
     */
    public function up(): void
    {
        Schema::create('versiculo', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_capitulo_block')->unsigned();

            // ESTE SERA LA POSICION
            $table->integer('posicion');

            $table->boolean('visible');

            $table->foreign('id_capitulo_block')->references('id')->on('biblia_capitulo_bloque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiculo');
    }
};
