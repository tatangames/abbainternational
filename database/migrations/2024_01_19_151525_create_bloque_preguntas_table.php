<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BLOQUE DE PREGUNTAS PARA CADA DEVOCIOANL
     */
    public function up(): void
    {
        Schema::create('bloque_preguntas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_plan_block_detalle')->unsigned();
            $table->bigInteger('id_imagen_pregunta')->unsigned();

            $table->integer('posicion');

            $table->foreign('id_plan_block_detalle')->references('id')->on('planes_block_detalle');
            $table->foreign('id_imagen_pregunta')->references('id')->on('imagen_preguntas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloque_preguntas');
    }
};
