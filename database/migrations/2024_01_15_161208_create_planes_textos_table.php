<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TEXTOS SEGUN IDIOMAS PARA LOS PLANES
     */
    public function up(): void
    {
        Schema::create('planes_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();
            $table->string('titulo', 150);
            $table->string('subtitulo', 50)->nullable();


            $table->foreign('id_planes')->references('id')->on('planes');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_textos');
    }
};
