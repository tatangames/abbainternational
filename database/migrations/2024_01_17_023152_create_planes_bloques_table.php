<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esto contiene las cajitas que muestran el devocional de cada dia
     * si esta visible siempre se mostrara
     */
    public function up(): void
    {
        Schema::create('planes_bloques', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes')->unsigned();

            // Fecha del bloque
            $table->dateTime('fecha_inicio');

            // esto oculta al usuario
            $table->boolean('visible');

            // desea cambiar en vez de mostrar la fecha en el bloque, un texto
            // personalizado, el texto se obtiene de bloques textos
            $table->boolean('texto_personalizado');

            $table->foreign('id_planes')->references('id')->on('planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_bloques');
    }
};
