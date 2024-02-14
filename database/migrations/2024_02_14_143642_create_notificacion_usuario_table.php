<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HISTORIAL DE NOTIFICACIONES PARA EL USUARIO
     */
    public function up(): void
    {
        Schema::create('notificacion_usuario', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_tipo_notificacion')->unsigned();

            $table->date('fecha');

            $table->foreign('tipo_tipo_notificacion')->references('id')->on('tipo_notificacion');
            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion_usuario');
    }
};
