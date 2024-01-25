<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FECHA CUANDO GANO EL HITO, OSEA ALCANZO EL NIVEL
     */
    public function up(): void
    {
        Schema::create('insignias_usuarios_detalle', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_niveles_insignias')->unsigned();
            $table->bigInteger('id_usuarios')->unsigned();

            $table->date('fecha');

            $table->foreign('id_niveles_insignias')->references('id')->on('niveles_insignias');
            $table->foreign('id_usuarios')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insignias_usuarios_detalle');
    }
};
