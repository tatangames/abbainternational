<?php

namespace Database\Seeders;

use App\Models\ZonaHoraria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ZonaHorariaSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ZonaHoraria::create([ // ID: 1
            'id_pais' => '1',
            'zona' => 'America/El_Salvador'
        ]);

        ZonaHoraria::create([ // ID: 2
            'id_pais' => '2',
            'zona' => 'America/Guatemala'
        ]);

        ZonaHoraria::create([ // honduras // ID: 3
            'id_pais' => '3',
            'zona' => 'America/Tegucigalpa'
        ]);

        ZonaHoraria::create([ // nicaragua // ID: 4
            'id_pais' => '4',
            'zona' => 'America/Managua'
        ]);

        ZonaHoraria::create([ // mexico // ID: 1
            'id_pais' => '5',
            'zona' => 'America/Mexico_City'
        ]);
    }
}
