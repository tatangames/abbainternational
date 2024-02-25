<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ESTOS CAPITULOS SE MOSTRARAN
     */
    public function up(): void
    {
        Schema::create('devocional_capitulo', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_devocional_biblia')->unsigned();
            $table->bigInteger('id_capitulo_bloque')->unsigned();



            $table->foreign('id_devocional_biblia')->references('id')->on('devocional_biblia');
            $table->foreign('id_capitulo_bloque')->references('id')->on('biblia_capitulo_bloque');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devocional_capitulo');
    }
};
