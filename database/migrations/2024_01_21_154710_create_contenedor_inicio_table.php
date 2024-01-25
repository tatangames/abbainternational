<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * LOS CONTENEDORES PARA BLOQUES INICIO Y SUS POSICIONES
     */
    public function up(): void
    {
        Schema::create('contenedor_inicio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50); // solo vera el admin
            $table->integer('posicion');
            $table->boolean('visible');


            $table->foreign('id_tipo_contenedor')->references('id')->on('tipo_contenedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenedor_inicio');
    }
};
