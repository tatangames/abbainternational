<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HASTA QUE HAYA UN REGISTRO PODRA HABILITAR TABLA planes_block_detalle
     * columna visible, sera devocional y preguntas requeridos, aunque en app
     * permite que solo devocional se muestre
     */
    public function up(): void
    {
        Schema::create('bloque_cuestionario_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_bloque_detalle')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();


            // EN EL API SE ARMA EL RESPONSE CON LOS DATOS


            // TITULO QUE AL TOCARLO ME MANDA A A BIBLIA
            $table->text('titulo');

            // TEXTO DEVOCIONAL
            $table->text('titulo_dia');

            $table->foreign('id_bloque_detalle')->references('id')->on('planes_block_detalle');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloque_cuestionario_textos');
    }
};
