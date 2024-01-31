<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DEPARTAMENTOS
     */
    public function up(): void
    {
        Schema::create('departamento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->bigInteger('id_pais')->unsigned();
            $table->bigInteger('id_zona_horaria')->unsigned();


            $table->foreign('id_pais')->references('id')->on('pais');
            $table->foreign('id_zona_horaria')->references('id')->on('zona_horaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamento');
    }
};
