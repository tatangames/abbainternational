<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iglesia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);

            $table->bigInteger('id_departamento')->unsigned();

            $table->foreign('id_departamento')->references('id')->on('departamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iglesia');
    }
};
