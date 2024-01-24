<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ALGUNOS DATOS PARA EL SISTEMA
     */
    public function up(): void
    {
        Schema::create('ajuste_sistema', function (Blueprint $table) {
            $table->id();
            // true: aparece segun fecha, sino hay de hoy, tomara el ultimo
            $table->boolean('refran_automatico');

            // true: aparece segun fecha, sino hay de hoy, tomara el ultimo
            $table->boolean('guia_automatico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajuste_sistema');
    }
};
