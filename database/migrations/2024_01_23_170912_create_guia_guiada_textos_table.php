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
        Schema::create('guia_guiada_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_guia_guiada')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('texto_1', 100);
            $table->string('texto_2', 200)->nullable();

            $table->foreign('id_guia_guiada')->references('id')->on('guia_guiada');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guia_guiada_textos');
    }
};
