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
        Schema::create('notificacion_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tiponoti_textos')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('descripción', 60);

            $table->foreign('id_tiponoti_textos')->references('id')->on('tipo_notificacion_textos');
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
