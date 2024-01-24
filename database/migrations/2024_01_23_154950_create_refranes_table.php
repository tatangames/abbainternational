<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LISTADO DE REFRANES
     */
    public function up(): void
    {
        Schema::create('refranes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->boolean('visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refranes');
    }
};
