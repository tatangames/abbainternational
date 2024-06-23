<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LISTA DE USUARIOS REGISTRADOS
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

            // el usuario tiene 20 caracteres para contraseña
            $table->string('password', 255);
            $table->string('codigo_pass', 10)->nullable();
            $table->string('version_registro', 100);
            $table->date('fecha_nacimiento');
            $table->dateTime('fecha_registro');


            // en Comunidad, cuando se envia una solicitud a un amigo
            // el otro telefono recibe notificacion y hay que saber su idioma
            // esto se setea al iniciar Fragment inicio siempre
            $table->integer('idioma_noti');

            // foto del usuario
            $table->string('imagen', 100)->nullable();

            // 6/22/2024
            // EL USUARIO AL REGISTRARSE O ABRIR INICIO TAB SE REGISTRA SU IDENTIFICADOR,
            // AL INICIAR SESION NO SE PEDIRA YA
            $table->string('onesignal',200)->nullable();


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
