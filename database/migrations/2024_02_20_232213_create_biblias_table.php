<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LISTADO DE BIBLIAS
     */
    public function up(): void
    {
        Schema::create('biblias', function (Blueprint $table) {
            $table->id();
            $table->boolean('visible');
            $table->integer('posicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biblias');
    }
};
