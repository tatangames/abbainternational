<?php

namespace Database\Seeders;

use App\Models\TipoInsignias;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsigniasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // 1- COMPARTIR APLICACION
        TipoInsignias::create([
            'imagen' => 'xx',
            'visible' => 1
        ]);


        // 2- COMPARTIR DEVOCIONAL
        TipoInsignias::create([
            'imagen' => 'xx',
            'visible' => 1
        ]);

        // 3- PLANES FINALIZADO
        TipoInsignias::create([
            'imagen' => 'xx',
            'visible' => 1
        ]);

        // 4- RACHAS DIA LECTURA
        TipoInsignias::create([
            'imagen' => 'xx',
            'visible' => 1
        ]);

        // 5- DEVOCIONAL FINALIZADO EN GRUPOS
        TipoInsignias::create([
            'imagen' => 'xx',
            'visible' => 1
        ]);

    }
}
