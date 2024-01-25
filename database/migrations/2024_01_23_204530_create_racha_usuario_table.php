<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SUMARAN TODAS LAS FECHAS SEGUIDAS, SINO SE BORRAN
     */
    public function up(): void
    {
        Schema::create('racha_usuario', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuarios')->unsigned();

            $table->date('fecha');

            $table->foreign('id_usuarios')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racha_usuario');
    }
};
