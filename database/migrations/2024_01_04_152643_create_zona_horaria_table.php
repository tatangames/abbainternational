<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CADA PAIS TIENE SU ZONA HORARIA
     */
    public function up(): void
    {
        Schema::create('zona_horaria', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_pais')->unsigned();
            $table->string('zona', 50);

            $table->foreign('id_pais')->references('id')->on('pais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zona_horaria');
    }
};
