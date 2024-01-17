<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * esto seran los diferentes idiomas de los textos de las cajitas, cada vez que se crea un idioma nuevo
     * aparecera en vista web el bloque de idioma faltante por agregar
     */
    public function up(): void
    {
        Schema::create('planes_block_deta_textos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_block_deta_textos');
    }
};
