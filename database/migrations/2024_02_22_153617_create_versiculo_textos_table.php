<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TEXTO DE CADA VERSICULO SEGUN IDIOMA
     */
    public function up(): void
    {
        Schema::create('versiculo_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_versiculo')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            // mayormente seran numeros
            $table->string('titulo', 50);

            $table->foreign('id_versiculo')->references('id')->on('versiculo');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiculo_textos');
    }
};
