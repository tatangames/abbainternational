<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * esto seran los items de cada cajita,
     * donde estan los circulos para marcar con check
     */
    public function up(): void
    {
        Schema::create('planes_block_detalle', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_planes_bloques')->unsigned();
            $table->boolean('completado');
            $table->integer('posicion');

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
