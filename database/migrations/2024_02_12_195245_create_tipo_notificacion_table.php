<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HISTORICO DE NOTIFICICACIONES
     */
    public function up(): void
    {
        Schema::create('tipo_notificacion', function (Blueprint $table) {
            $table->id();

            $table->string('imagen', 100)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_notificacion');
    }
};
