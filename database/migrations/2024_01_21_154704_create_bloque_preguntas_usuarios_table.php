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
        Schema::create('bloque_preguntas_usuarios', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_bloque_preguntas')->unsigned();
            $table->bigInteger('id_usuarios')->unsigned();

            // limite lo pondra limit input
            $table->text('texto')->nullable();

            // nomas la respondio
            $table->dateTime('fecha');

            // fecha actualizo respuesta
            $table->dateTime('fecha_actualizo')->nullable();

            $table->foreign('id_bloque_preguntas')->references('id')->on('bloque_preguntas');
            $table->foreign('id_usuarios')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloque_preguntas_usuarios');
    }
};
