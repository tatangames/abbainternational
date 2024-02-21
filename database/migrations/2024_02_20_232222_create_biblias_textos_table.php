<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA BIBLIA PARA UN IDIOMA
     */
    public function up(): void
    {
        Schema::create('biblias_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_biblias')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('titulo', 50);

            $table->foreign('id_biblias')->references('id')->on('biblias');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblias_textos');
    }
};
