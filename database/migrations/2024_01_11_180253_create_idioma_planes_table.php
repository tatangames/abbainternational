<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ID DE IDIOMAS; ESPANOL, INGLES
     * se podra ir creando con el sistema
     * si hay un nuevo idioma, se detecta y se debera crear en vista web su texto correspondiente
     */
    public function up(): void
    {
        Schema::create('idioma_planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);

            // solo afectara si se agrega un tercer idioma en adelante
            $table->boolean('disponible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idioma_planes');
    }
};
