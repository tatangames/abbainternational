<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REFRANES SEGUN IDIOMA
     */
    public function up(): void
    {
        Schema::create('refranes_textos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_refranes')->unsigned();
            $table->bigInteger('id_idioma_planes')->unsigned();

            $table->string('texto_1', 100)->nullable();
            $table->string('texto_2', 800);
            $table->string('texto_3', 100)->nullable();


            $table->foreign('id_refranes')->references('id')->on('refranes');
            $table->foreign('id_idioma_planes')->references('id')->on('idioma_planes');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refranes_textos');
    }
};
