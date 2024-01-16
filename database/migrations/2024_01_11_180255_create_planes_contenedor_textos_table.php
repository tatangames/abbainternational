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
        Schema::create('planes_contenedor_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes_contenedor')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('titulo', 75);

            $table->foreign('id_planes_contenedor')->references('id')->on('planes_contenedor');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_contenedor_textos');
    }
};
