<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LOS DIFERENTES NIVELES DE LAS INSIGNIAS
     */
    public function up(): void
    {
        Schema::create('niveles_insignias', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipo_insignia')->unsigned();
            $table->integer('nivel');

            $table->foreign('id_tipo_insignia')->references('id')->on('tipo_insignias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveles_insignias');
    }
};
