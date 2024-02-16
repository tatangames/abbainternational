<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LOS PLANES QUE ESTEN AQUI, NO LOS VERA EL USUARIO EN COMUNIDAD
     */
    public function up(): void
    {
        Schema::create('ocultar_planes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_planes')->unsigned();

            $table->foreign('id_usuario')->references('id')->on('usuarios');
            $table->foreign('id_planes')->references('id')->on('planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocultar_planes');
    }
};
