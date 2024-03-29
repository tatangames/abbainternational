<?php

namespace Database\Seeders;

use App\Models\InsigniasTextos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsigniasTextosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1- COMPARTIR APLICACION

        InsigniasTextos::create([
            'id_tipo_insignia' => '1',
            'id_idioma_planes' => '1',
            'texto_1' => 'Compartir Aplicación',
            'texto_2' => 'Entre más compartas la aplicación aumentas tu nivel de insignia',
        ]);

        InsigniasTextos::create([
            'id_tipo_insignia' => '1',
            'id_idioma_planes' => '2',
            'texto_1' => 'Share Application',
            'texto_2' => 'The more you share the app you increase your badge level',
        ]);



        // 2- COMPARTIR DEVOCIONAL

        InsigniasTextos::create([
            'id_tipo_insignia' => '2',
            'id_idioma_planes' => '1',
            'texto_1' => 'Compartir Devocional',
            'texto_2' => 'Entre más compartas el Devocional, aumentas tu nivel de insignia',
        ]);

        InsigniasTextos::create([
            'id_tipo_insignia' => '2',
            'id_idioma_planes' => '2',
            'texto_1' => 'Share Devotional',
            'texto_2' => 'The more you share the Devotional, increase your badge level',
        ]);




        // 3- PLANES FINALIZADOS

        InsigniasTextos::create([
            'id_tipo_insignia' => '3',
            'id_idioma_planes' => '1',
            'texto_1' => 'Finalizar un Plan',
            'texto_2' => 'Entre más Planes finalices, aumentas tu nivel de insignia',
        ]);

        InsigniasTextos::create([
            'id_tipo_insignia' => '3',
            'id_idioma_planes' => '2',
            'texto_1' => 'Finalize a Plan',
            'texto_2' => 'The more you share the Devotional, increase your badge level',
        ]);


        // 4- RACHA DIA LECTURA

        InsigniasTextos::create([
            'id_tipo_insignia' => '4',
            'id_idioma_planes' => '1',
            'texto_1' => 'Rachas / Días lectura y devocional',
            'texto_2' => 'Entre más devocionales completes, aumentas tu nivel de insignia',
        ]);

        InsigniasTextos::create([
            'id_tipo_insignia' => '4',
            'id_idioma_planes' => '2',
            'texto_1' => 'Streaks / Reading and devotional days',
            'texto_2' => 'The more devotionals you complete, increase your badge level',
        ]);


        // 5- DEVOCIONAL FINALIZADOS EN GRUPOS

        InsigniasTextos::create([
            'id_tipo_insignia' => '5',
            'id_idioma_planes' => '1',
            'texto_1' => 'Planes Finalizados en grupos',
            'texto_2' => 'Cuando completen tus amigos el plan, ganaras 1 punto por cada plan completado por tus amigos',
        ]);

        InsigniasTextos::create([
            'id_tipo_insignia' => '5',
            'id_idioma_planes' => '2',
            'texto_1' => 'Finalized plans in groups',
            'texto_2' => 'When your friends complete the plan, you will earn 1 point for each plan completed by your friends',
        ]);


    }
}
