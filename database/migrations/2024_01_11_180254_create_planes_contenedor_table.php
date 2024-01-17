<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contenedor de los Planes, esto seria el Titulo superior
     */
    public function up(): void
    {
        Schema::create('planes_contenedor', function (Blueprint $table) {
            $table->id();
            $table->boolean('visible');
            $table->integer('posicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_contenedor');
    }
};
