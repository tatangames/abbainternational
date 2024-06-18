<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LIBROS PARA LA BIBLIA
     */
    public function up(): void
    {
        Schema::create('biblia_capitulos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_biblias')->unsigned();

            $table->boolean('visible');
            $table->integer('posicion');

            $table->foreign('id_biblias')->references('id')->on('biblias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblia_capitulos');
    }
};
