<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TEXTOS PARA CUANDO SE GANE UNA NOTIFICACION PUSH
     * Y TAMBIEN EL HISTORIAL QUE QUEDA GUARDADO
     */
    public function up(): void
    {
        Schema::create('notificacion_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipo_notificacion')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('titulo', 25);
            $table->string('descripcion', 60);

            $table->foreign('id_tipo_notificacion')->references('id')->on('tipo_notificacion');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion_textos');
    }
};
