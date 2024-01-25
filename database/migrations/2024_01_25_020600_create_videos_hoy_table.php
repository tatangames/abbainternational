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
        Schema::create('videos_hoy', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipo_video')->unsigned();

            // sera la portada de presentacion
            $table->string('imagen', 100);
            $table->string('url_video', 100);
            $table->integer('posicion');

            $table->foreign('id_tipo_video')->references('id')->on('tipo_video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos_hoy');
    }
};
