<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bloque_preguntas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_plan_block_detalle')->unsigned();

            $table->boolean('visible');
            $table->integer('posicion');

            $table->foreign('id_plan_block_detalle')->references('id')->on('planes_block_detalle');
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
