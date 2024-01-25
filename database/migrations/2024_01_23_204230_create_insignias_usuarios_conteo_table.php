<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CONTEO PARA GANARSE NIVEL DE INSIGINIA
     */
    public function up(): void
    {
        Schema::create('insignias_usuarios_conteo', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipo_insignia')->unsigned();
            $table->bigInteger('id_usuarios')->unsigned();

            // solo podra seguir sumando si es menor al maximo de puntos permitido
            $table->integer('conteo');

            $table->foreign('id_tipo_insignia')->references('id')->on('tipo_insignias');
            $table->foreign('id_usuarios')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insignias_usuarios_conteo');
    }
};
