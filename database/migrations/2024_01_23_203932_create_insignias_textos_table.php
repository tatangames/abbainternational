<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TITULO DE LA INSIGINIAS Y DESCRIPCIONES
     */
    public function up(): void
    {
        Schema::create('insignias_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipo_insignia')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('texto_1', 200);
            $table->string('texto_2', 200)->nullable();

            // textos para notificaciones
            $table->string('titulo_notificacion', 50);
            $table->string('descripcion_notificacion', 60);

            $table->foreign('id_tipo_insignia')->references('id')->on('tipo_insignias');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insignias_textos');
    }
};
