<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * se almacena el block detalle y se redirecciona al usuario,
     * sino esta registrado se debera registrar de una vez
     *
     * // HABRA VARIOS REGISTROS, Y LA FECHA SERA UNICA.
     */
    public function up(): void
    {
        Schema::create('lectura_dia', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes_block_detalle')->unsigned();

            $table->foreign('id_planes_block_detalle')->references('id')->on('planes_block_detalle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectura_dia');
    }
};
