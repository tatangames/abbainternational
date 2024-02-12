<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * QUEDA REGISTRADA LA RACHA MAS ALTA ALCANZADA
     */
    public function up(): void
    {
        Schema::create('racha_alta', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuarios')->unsigned();

            // esto seria la racha alta que queda guardada
            $table->integer('contador');

            $table->foreign('id_usuarios')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racha_alta');
    }
};
