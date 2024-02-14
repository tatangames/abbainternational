<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SE LLEVA UN CONTROL DE LOS PLANES FINALIZADOS POR USUARIO
     */
    public function up(): void
    {
        Schema::create('planes_finalizado_usuario', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes')->unsigned();
            $table->bigInteger('id_usuario')->unsigned();

            $table->foreign('id_planes')->references('id')->on('planes');
            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_finalizado_usuario');
    }
};
