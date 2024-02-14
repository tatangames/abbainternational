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

        NotificacionTextos::create([ // ID 1
            'id_tipo_insignia' => '1',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Compartir App',
            'descripcion_hito' => 'Subiste de Nivel tu insignia Compartir App',
        ]);


        NotificacionTextos::create([ // ID 2
            'id_tipo_insignia' => '1',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You earned a Share App Badge',
            'descripcion_hito' => 'You leveled up your badge Share App',
        ]);




        // ** INSIGNIA COMPARTIR DEVOCIONAL **

        NotificacionTextos::create([ // ID 3
            'id_tipo_insignia' => '2',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Compartir Devocional',
            'descripcion_hito' => 'Subiste de Nivel tu insignia Compartir Devocional',
        ]);


        NotificacionTextos::create([ // ID 4
            'id_tipo_insignia' => '2',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You Earned Devotional Share Badge',
            'descripcion_hito' => 'You leveled up your badge Share Devotional',
        ]);


        // ** INSIGNIA PLANES FINALIZADOS **

        NotificacionTextos::create([ // ID 5
            'id_tipo_insignia' => '3',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Plan Finalizado',
            'descripcion_hito' => 'Subiste de Nivel tu insignia Plan Finalizado',
        ]);


        NotificacionTextos::create([ // ID 6
            'id_tipo_insignia' => '3',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You earned a Finished Plan Badge',
            'descripcion_hito' => 'You leveled up your Finished Plan badge',
        ]);



        // ** INSIGNIA RACHA DIA LECTURA **

        NotificacionTextos::create([ // ID 7
            'id_tipo_insignia' => '4',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Racha Lectura',
            'descripcion_hito' => 'Subiste de Nivel tu insignia Racha Lectura',
        ]);


        NotificacionTextos::create([ // ID 8
            'id_tipo_insignia' => '4',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You Earned Reading Streak Badge',
            'descripcion_hito' => 'You leveled up your Reading Streak badge',
        ]);


        // ** INSIGNIA PLANES FINALIZADOS EN GRUPOS **

        NotificacionTextos::create([ // ID 9
            'id_tipo_insignia' => '4',
            'id_idioma_planes' => '1',
            'titulo' => 'Mi Caminar con Dios',
            'descripcion' => 'Ganaste Insignia Plan Grupal',
            'descripcion_hito' => 'Subiste de Nivel tu insignia Planes en Grupo',
        ]);


        NotificacionTextos::create([ // ID 10
            'id_tipo_insignia' => '4',
            'id_idioma_planes' => '2',
            'titulo' => 'My Walk with God',
            'descripcion' => 'You earned a Group Plan Badge',
            'descripcion_hito' => 'You leveled up your Group Plans badge',
        ]);


    }
}
