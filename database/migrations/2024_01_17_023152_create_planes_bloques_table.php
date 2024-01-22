<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esto contiene las cajitas que muestran el devocional de cada dia
     * el usuario recupera esto segun horario de la iglesia que esta registrado
     * ahi esta su zona horaria
     */
    public function up(): void
    {
        Schema::create('planes_bloques', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_planes')->unsigned();
            $table->dateTime('fecha_inicio');

            // esto oculta al usuario
            $table->boolean('visible');

            // desea cambiar en vez de mostrar la fecha en el bloque, un texto
            // personalizado
            $table->boolean('texto_personalizado');

            // esto hace que cuando usuario toque una cajita, verificar si puede
            // ver el contenido, sino deberar esperar la fecha de inicio
            $table->boolean('esperar_fecha');

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
