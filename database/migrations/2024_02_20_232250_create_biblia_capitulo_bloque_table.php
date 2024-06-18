<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA BLOQUE PARA EL CAPITULO, REPRESENTADO CON UN NUMERO
     */
    public function up(): void
    {
        Schema::create('biblia_capitulo_bloque', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_biblia_capitulo')->unsigned();

            // ESTE SERA LA POSICION
            $table->integer('posicion');


            $table->boolean('visible');

            $table->foreign('id_biblia_capitulo')->references('id')->on('biblia_capitulos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblia_capitulo_bloque');
    }
};
