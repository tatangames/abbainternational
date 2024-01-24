<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AQUI SE DICE EL TIPO QUE MOSTRAR Y LA POSICION, SOLO HABRA 1 REGISTRO
     *  O BORRAR PARA QUE NO BUSQUE
     */
    public function up(): void
    {
        Schema::create('contenedor_inicio', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipo_contenedor')->unsigned();
            $table->integer('posicion');


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
