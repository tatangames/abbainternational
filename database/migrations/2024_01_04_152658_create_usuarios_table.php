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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_iglesia')->unsigned();
            $table->bigInteger('id_genero')->unsigned();
            $table->string('nombre', 50);
            $table->string('apellido', 50);
            $table->string('correo', 100);
            $table->string('password', 255);
            $table->string('version_registro', 100);
            $table->boolean('recibir_notificacion');
            $table->string('onesignal')->nullable();
            $table->string('edad', 10);


            $table->foreign('id_iglesia')->references('id')->on('iglesia');
            $table->foreign('id_genero')->references('id')->on('generos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
