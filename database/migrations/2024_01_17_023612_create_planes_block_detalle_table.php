<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * esto seran los items de cada cajita,
     * donde estan los circulos para marcar con check
     *
     * // SE AGREGO UN REDIRECCIONAMIENTO A URL SI ESTA ACTIVADO EL CHECK
     */
    public function up(): void
    {
        Schema::create('planes_block_detalle', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_planes_bloques')->unsigned();
            $table->integer('posicion');
            $table->boolean('visible');

            // TRUE: IGNORA EL PRIMER CUADRO PARA COMPARTIR PREGUNTA
            $table->boolean('ignorar_pregunta');

            // redireccionamiento web
            $table->boolean('redireccionar_web');
            $table->string('url_link', 1000)->nullable();


            $table->foreign('id_planes_bloques')->references('id')->on('planes_bloques');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_block_detalle');
    }
};
