<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ENVIO DE SOLICUTD A UN AMIGO
     */
    public function up(): void
    {
        Schema::create('comunidad_solicitud', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_usuario_envia')->unsigned();
            $table->bigInteger('id_usuario_recibe')->unsigned();

            $table->dateTime('fecha');
            $table->boolean('estado');


            $table->foreign('id_usuario_envia')->references('id')->on('usuarios');
            $table->foreign('id_usuario_recibe')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunidad_solicitud');
    }
};
