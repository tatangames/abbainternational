<?php

namespace Database\Seeders;

use App\Models\NotificacionTextos;
use App\Models\TipoNotificacionTextos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificacionTextoSeeder extends Seeder
{
    /**
     * TEXTOS PARA NOTIFICIACIONES
     */
    public function run(): void
    {

        // ** INSIGNIA COMPARTIR APP **

        NotificacionTextos::create([
            'id_tipo_notificacion' => '1',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Compartir App',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '1',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You earned a Share App Badge',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '2',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Subiste de Nivel tu insignia Compartir App',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '2',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You leveled up your badge Share App',
        ]);









        // ** INSIGNIA COMPARTIR DEVOCIONAL **

        NotificacionTextos::create([
            'id_tipo_notificacion' => '3',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Compartir Devocional',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '3',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You Earned Devotional Share Badge',
        ]);



        NotificacionTextos::create([
            'id_tipo_notificacion' => '4',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Subiste de Nivel tu insignia Compartir Devocional',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '4',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You leveled up your badge Share Devotional',
        ]);











        // ** INSIGNIA PLANES FINALIZADOS **

        NotificacionTextos::create([
            'id_tipo_notificacion' => '5',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Plan Finalizado',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '5',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You earned a Finished Plan Badge',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '6',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Subiste de Nivel tu insignia Plan Finalizado',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '6',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You leveled up your Finished Plan badge',
        ]);










        // ** INSIGNIA RACHA DIA LECTURA **

        NotificacionTextos::create([
            'id_tipo_notificacion' => '7',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Racha Lectura',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '7',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You Earned Reading Streak Badge',
        ]);

        NotificacionTextos::create([
            'id_tipo_notificacion' => '8',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Subiste de Nivel tu insignia Racha Lectura',
        ]);

        NotificacionTextos::create([
            'id_tipo_notificacion' => '8',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You leveled up your Reading Streak badge',
        ]);








        // ** INSIGNIA PLANES FINALIZADOS EN GRUPOS **

        NotificacionTextos::create([
            'id_tipo_notificacion' => '9',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Plan Grupal',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '9',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You earned a Group Plan Badge',
        ]);




        NotificacionTextos::create([
            'id_tipo_notificacion' => '10',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Subiste de Nivel tu insignia Planes en Grupo',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '10',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You leveled up your Group Plans badge',
        ]);






        // ** UN AMIGO TE ACABA DE ENVIAR SOLICITUD **

        NotificacionTextos::create([
            'id_tipo_notificacion' => '11',
            'id_idioma_planes' => '1',
            'titulo' => 'Solicitud Nueva',
            'descripcion' => 'Un amigo te acaba de enviar una nueva solicitud',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '11',
            'id_idioma_planes' => '2',
            'titulo' => 'New friend request',
            'descripcion' => 'A friend just sent you a new request',
        ]);


        // ** UN AMIGO ACABA DE ACEPTAR TU SOLICITUD **


        NotificacionTextos::create([
            'id_tipo_notificacion' => '12',
            'id_idioma_planes' => '1',
            'titulo' => 'Solicitud Aceptada',
            'descripcion' => 'Un amigo te acaba de aceptar la Solicitud',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '12',
            'id_idioma_planes' => '2',
            'titulo' => 'Request accepted',
            'descripcion' => 'A friend just accepted your request',
        ]);



        // ** XX ACABA DE UNIRTE A UN PLAN GRUPAL **


        NotificacionTextos::create([
            'id_tipo_notificacion' => '13',
            'id_idioma_planes' => '1',
            'titulo' => 'Plan Grupal',
            'descripcion' => 'Un Amigo te acaba de unir a su Plan Grupal',
        ]);


        NotificacionTextos::create([
            'id_tipo_notificacion' => '13',
            'id_idioma_planes' => '2',
            'titulo' => 'Group Plan',
            'descripcion' => 'A friend just joined you in her Group Plan',
        ]);
    }
}
