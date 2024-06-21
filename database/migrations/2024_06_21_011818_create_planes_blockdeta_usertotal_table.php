<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ES PARA LLEVAR CONTADOS LOS DIAS SEGUIDOS AL REALIZAR DEVOCIONAL
     * SI HACE 2 DEVOCIONALES EL MISMO DIA NO SE CUENTA
     * SE CREA AL TOCAR CHECK UNICAMENTE
     *
     */
    public function up(): void
    {
        Schema::create('planes_blockdeta_usertotal', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_planes_block_deta');
            $table->date('fecha');

            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_blockdeta_usertotal');
    }
};
