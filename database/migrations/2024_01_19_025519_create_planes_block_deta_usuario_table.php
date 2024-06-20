<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA VEZ QUE HACE CHECK EL USUARIO, SE VERIFICA SI YA ESTA CREADA UNA FILA CON ESE BLOQUE
     * SINO SE CREA
     *
     *
     */
    public function up(): void
    {
        Schema::create('planes_block_deta_usuario', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_planes_block_deta')->unsigned();


            // 10/06/2024
            // SE AGREGO FECHA QUE ES COMPLETADO, SE SETEA UNICAMENTE AL CREARSE LA FILA
            $table->date('fecha');

            // 19/06/2024
            // SIEMPRE SE DEJARA, CON ESTO VOY A SABER SI FINALIZO EL PLAN
            $table->boolean('completado');

            $table->foreign('id_usuario')->references('id')->on('usuarios');
            $table->foreign('id_planes_block_deta')->references('id')->on('planes_block_detalle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_block_deta_usuario');
    }
};
