<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bloque_preguntas_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_bloque_preguntas')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->text('texto');

            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
            $table->foreign('id_bloque_preguntas')->references('id')->on('bloque_preguntas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloque_preguntas_textos');
    }
};
