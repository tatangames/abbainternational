<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TODOS LOS IDIOMAS PARA MENSAJES DEL SISTEMA
     */
    public function up(): void
    {
        Schema::create('idioma_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('espanol', 300);
            $table->string('ingles', 300);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idioma_sistema');
    }
};
