<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RIDIOMA DE TEXTOS PARA LOS VIDEOS
     */
    public function up(): void
    {
        Schema::create('videos_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_videos_hoy')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();
            $table->string('titulo', 100)->nullable();

            $table->foreign('id_videos_hoy')->references('id')->on('videos_hoy');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos_textos');
    }
};
