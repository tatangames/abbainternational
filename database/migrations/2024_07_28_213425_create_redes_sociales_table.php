<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REDIRECCIONAMIENTO REDES SOCIALES
     */
    public function up(): void
    {
        Schema::create('redes_sociales', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 100);
            $table->string('imagen', 100);
            $table->string('link', 800);
            $table->integer('posicion');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redes_sociales');
    }
};
