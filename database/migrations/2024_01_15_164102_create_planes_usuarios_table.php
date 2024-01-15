<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AQUI ESTA EL USUARIO Y EL PLAN SELECCIONADO
     */
    public function up(): void
    {
        Schema::create('planes_usuarios', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_planes')->unsigned();


            $table->foreign('id_usuario')->references('id')->on('usuarios');
            $table->foreign('id_planes')->references('id')->on('planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_usuarios');
    }
};
