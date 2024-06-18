<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IDIOMA PARA EL LIBRO DE LA BIBLIA
     */
    public function up(): void
    {
        Schema::create('biblia_capitulos_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_biblia_capitulo')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('titulo', 50);

            $table->foreign('id_biblia_capitulo')->references('id')->on('biblia_capitulos');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblia_capitulos_textos');
    }
};
