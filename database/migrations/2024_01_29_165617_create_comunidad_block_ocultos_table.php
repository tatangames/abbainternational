<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PARA QUE EL USUARIO PUEDA OCULTAR SUS PREGUNTAS
     */
    public function up(): void
    {
        Schema::create('comunidad_block_ocultos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_plan_block_deta')->unsigned();

            $table->foreign('id_usuario')->references('id')->on('usuarios');
            $table->foreign('id_plan_block_deta')->references('id')->on('planes_block_detalle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunidad_block_ocultos');
    }
};
