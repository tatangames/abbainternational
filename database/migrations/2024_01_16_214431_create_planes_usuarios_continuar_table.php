<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SOLO HABRA 1 REGISTRO POR USUARIO, UTILIZADO PARA MOSTRAR AL USAURIO
     * CUAL FUE EL ULTIMO PLAN QUE REALIZO ALGO
     */
    public function up(): void
    {
        Schema::create('planes_usuarios_continuar', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuarios')->unsigned();
            $table->bigInteger('id_planes')->unsigned();

            $table->foreign('id_usuarios')->references('id')->on('usuarios');
            $table->foreign('id_planes')->references('id')->on('planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_usuarios_continuar');
    }
};
