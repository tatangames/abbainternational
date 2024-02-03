<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NUMERACION DE IMAGENES PARA LAS PREGUNTAS, ES EL ICONO
     */
    public function up(): void
    {
        Schema::create('imagen_preguntas', function (Blueprint $table) {
            $table->id();
            $table->string('imagen', 100);
            $table->string('nombre', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagen_preguntas');
    }
};
