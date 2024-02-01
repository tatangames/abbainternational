<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TODOS LOS PLANES PARA TODOS LOS USUARIOS
     * SERA UN LISTADO Y SE OBTENDRA CON PAGINACION
     */
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();

            // para la miniatura
            $table->string('imagen', 100);

            // para la miniatura
            $table->string('imagenportada', 100);

            $table->boolean('visible');

            $table->date('fecha');

            // para mostrar o no la barra de progreso, el calculo se hace a codigo
            $table->boolean('barra_progreso');

            $table->integer('posicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
