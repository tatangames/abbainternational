<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BLOQUE DE COMPARTIR INFORMACION APP, SOLO HABRA 1
     */
    public function up(): void
    {
        Schema::create('comparte_app', function (Blueprint $table) {
            $table->id();
            $table->string('imagen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparte_app');
    }
};
