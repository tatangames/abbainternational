<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * POR CADA DEVOCIONAL SE NECESITA UNA REFERENCIA A LA BIBLIA A CUAL REDIRECCIONAR
     */
    public function up(): void
    {
        Schema::create('devocional_biblia', function (Blueprint $table) {
            $table->id();


            $table->bigInteger('id_bloque_detalle')->unsigned();
            $table->bigInteger('id_biblia')->unsigned();



            $table->foreign('id_bloque_detalle')->references('id')->on('planes_block_detalle');
            $table->foreign('id_biblia')->references('id')->on('biblias');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devocional_biblia');
    }
};
