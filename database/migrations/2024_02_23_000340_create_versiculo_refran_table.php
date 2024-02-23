<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TEXTO FINAL DE UNA BIBLIA
     */
    public function up(): void
    {
        Schema::create('versiculo_refran', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_versiculo')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->text('titulo');

            $table->foreign('id_versiculo')->references('id')->on('versiculo');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiculo_refran');
    }
};
