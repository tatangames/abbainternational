<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA VEZ QUE GUARDA CON CHECK SE REGISTRA QUE HIZO UN DEVOCIONAL X FECHA
     * si quita el check no pasara nada
     */
    public function up(): void
    {
        Schema::create('racha_devocional', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_plan_block_deta')->unsigned();

            $table->date('fecha');

            $table->foreign('id_usuario')->references('id')->on('usuarios');
            $table->foreign('id_plan_block_deta')->references('id')->on('planes_block_detalle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racha_devocional');
    }
};
