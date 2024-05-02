<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CUANDO SE REGISTRA USUARIO Y SELECCIONA PAIS OTROS, DEBE COLOCAR TEXTO DEL PAIS
     * AUNQUE POR DEFECTO SE REGISTRARA EN UN DEPARTAMENTO Y UNA IGLESIA
     */
    public function up(): void
    {
        Schema::create('usuario_region_otros', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_usuario')->unsigned();

            $table->string('pais', 300);
            $table->string('ciudad', 300);


            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_region_otros');
    }
};
