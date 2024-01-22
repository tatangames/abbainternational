<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CUANDO EN VEZ DE MOSTRAR FECHA AL BLOQUE, QUIERE MOSTRAR UN TEXTO
     * COMO EJEMPLO DIA 1, DIA 2, Y ASI
     */
    public function up(): void
    {
        Schema::create('planes_bloques_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes_bloques')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('titulo', 20)->nullable();

            $table->foreign('id_planes_bloques')->references('id')->on('planes_bloques');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_bloques_textos');
    }
};
