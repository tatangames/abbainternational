<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PLANES AMIGOS PARA COMUNIDAD
     */
    public function up(): void
    {
        Schema::create('planes_amigos', function (Blueprint $table) {
            $table->id();

            // para saber si estado es 3; solicitud eliminado
            $table->bigInteger('id_comunidad_solicitud')->unsigned();
            $table->bigInteger('id_planes')->unsigned();

            // usuario que ganara los puntos
            $table->bigInteger('id_usuario')->unsigned();


            $table->foreign('id_comunidad_solicitud')->references('id')->on('comunidad_solicitud');
            $table->foreign('id_planes')->references('id')->on('planes');
            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_amigos');
    }
};
