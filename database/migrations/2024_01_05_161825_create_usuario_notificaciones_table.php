<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA VEZ QUE SE REGISTRE O INICIE SESION SE GUARDARA SU IDENTIFICADOR
     * SE COMPROBARA EN LOGIN QUE SI EL IDENTIFICADO ESTA REGISTRADO NO SE HARA NADA
     *
     */
    public function up(): void
    {
        Schema::create('usuario_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->string('onesignal')->nullable();

            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_notificaciones');
    }
};
