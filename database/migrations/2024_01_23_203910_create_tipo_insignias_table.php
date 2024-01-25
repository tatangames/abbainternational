<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TIPO DE LA INSIGNIA
     */
    public function up(): void
    {
        Schema::create('tipo_insignias', function (Blueprint $table) {
            $table->id();
            $table->string('imagen');

            // si esta oculta el usuario ya no puede verla
            $table->boolean('visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_insignias');
    }
};
