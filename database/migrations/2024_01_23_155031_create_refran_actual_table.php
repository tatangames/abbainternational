<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SI EN TABLA ajuste_sistema esta desactivado el bool, se mostrara este
     * refran seleccionado, si pone automatico se mostrar el la fecha actual,
     * sino tomara el ultimo disponible
     */
    public function up(): void
    {
        Schema::create('refran_actual', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_refranes')->unsigned();
            $table->dateTime('fecha'); // cuando fue registrado

            $table->foreign('id_refranes')->references('id')->on('refranes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refran_actual');
    }
};
