<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AQUI SE VINCULA AL USUARIO Y LA INSIGNIA GANADA
     */
    public function up(): void
    {
        Schema::create('insignias_usuarios', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipo_insignia')->unsigned();
            $table->bigInteger('id_usuario')->unsigned();

            // fecha gano la insignia
            $table->date('fecha');

            $table->foreign('id_usuario')->references('id')->on('usuarios');
            $table->foreign('id_tipo_insignia')->references('id')->on('tipo_insignias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insignias_usuarios');
    }
};
