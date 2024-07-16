<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IMAGENES DEL DIA, SOLO HABRA UN LISTADO DE IMAGENES
     */
    public function up(): void
    {
        Schema::create('imagenes_dia', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('descripcion', 100)->nullable();
            $table->string('imagen', 100);
            $table->integer('posicion');

            // para idioma ingles, que muestre esta imagen
            $table->string('imagen_ingles', 100);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes_dia');
    }
};
