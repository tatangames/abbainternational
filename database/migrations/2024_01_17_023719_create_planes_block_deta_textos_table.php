<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * esto seran los diferentes idiomas de los textos de las cajitas, cada vez que se crea un idioma nuevo
     * aparecera en vista web el bloque de idioma faltante por agregar
     */
    public function up(): void
    {
        Schema::create('planes_block_deta_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes_block_detalle')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();
            $table->string('titulo', 100);

            // titulo que va en la pregunta
            $table->string('titulop', 150)->nullable();

            $table->foreign('id_planes_block_detalle')->references('id')->on('planes_block_detalle');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_block_deta_textos');
    }
};
