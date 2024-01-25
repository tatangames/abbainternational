<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LISTA UNICA DE TIPO DE VIDEO, FACEBOOK, INSTAGRAM, YOUTUBE, SERVIDOR PROPIO
     */
    public function up(): void
    {
        Schema::create('tipo_video', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_video');
    }
};
