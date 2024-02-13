<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TIPO DE NOTIFICACION PARA LOS TEXTOS
     */
    public function up(): void
    {
        Schema::create('tipo_notificacion_textos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_notificacion_textos');
    }
};