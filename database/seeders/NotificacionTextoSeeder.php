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

        // ** GANASTE INSIGNIA COMPARTIR APP **


        // 200 caracteres
        TipoNotificacionTextos::create([ // ID 1
            'nombre' => 'Para cuando gane insignia por primera vez',
        ]);

        NotificacionTextos::create([
            'id_tiponoti_textos' => '1',
            'id_idioma_planes' => '1',
            'descripcion' => 'Ganaste la Insignia Compartir App',
        ]);

        NotificacionTextos::create([
            'id_tiponoti_textos' => '1',
            'id_idioma_planes' => '2',
            'descripcion' => 'You Earned the Share Badge App',
        ]);




        // ** AUMENTO NIVEL INSIGNIA COMPARTIR APP **

        TipoNotificacionTextos::create([ // ID 2
            'nombre' => 'Para cuando aumente de nivel insignia compartir app',
        ]);

        NotificacionTextos::create([
            'id_tiponoti_textos' => '2',
            'id_idioma_planes' => '1',
            'descripcion' => 'Subiste de nivel tu Insignia Compartir App',
        ]);

        NotificacionTextos::create([
            'id_tiponoti_textos' => '2',
            'id_idioma_planes' => '2',
            'descripcion' => 'You leveled up your Share App Badge',
        ]);




    }
}
