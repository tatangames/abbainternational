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
        Schema::create('planes_amigos_detalle', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_planes_amigos')->unsigned();

            // usuario que cuando complete devocional, dara punto a este usuario
            // se ganara cuando haga check
            $table->bigInteger('id_usuario')->unsigned();

            $table->foreign('id_planes_amigos')->references('id')->on('planes_amigos');
            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_amigos_detalle');
    }
};
