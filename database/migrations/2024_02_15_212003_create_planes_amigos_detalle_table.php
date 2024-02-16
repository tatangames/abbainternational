<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SE HARA REFERENCIA A PLANES_USUARIOS
     */
    public function up(): void
    {
        Schema::create('planes_amigos_detalle', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_planes_usuarios')->unsigned();

            $table->bigInteger('id_comunidad_solicitud')->unsigned();

            // usuario que cuando complete devocional, dara punto al de :: planes_usuarios
            // se ganara cuando haga check
            $table->bigInteger('id_usuario')->unsigned();

            $table->foreign('id_planes_usuarios')->references('id')->on('planes_usuarios');
            $table->foreign('id_comunidad_solicitud')->references('id')->on('comunidad_solicitud');
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
