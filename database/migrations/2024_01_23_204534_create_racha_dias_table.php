<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA VEZ QUE HABRA LA APP SE CONTARA COMO FECHA REGISTRADA
     */
    public function up(): void
    {
        Schema::create('racha_dias', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();

            $table->date('fecha');

            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racha_dias');
    }
};
