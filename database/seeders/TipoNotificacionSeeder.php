<?php

namespace Database\Seeders;

use App\Models\TipoNotificacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoNotificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // ** INSIGNIA COMPARTIR APP **
        TipoNotificacion::create([  // ID: 1
            'imagen' => null,
        ]);


        // ** INSIGNIA COMPARTIR APP - AUMENTO DE NIVEL **
        TipoNotificacion::create([  // ID: 2
            'imagen' => null,
        ]);






        // ** INSIGNIA COMPARTIR DEVOCIONAL **
        TipoNotificacion::create([ // ID: 3
            'imagen' => null,
        ]);


        // ** INSIGNIA COMPARTIR DEVOCIONAL - AUMENTO DE NIVEL**
        TipoNotificacion::create([ // ID: 4
            'imagen' => null,
        ]);







        // ** INSIGNIA PLANES FINALIZADOS **
        TipoNotificacion::create([ // ID: 5
            'imagen' => null,
        ]);


        // ** INSIGNIA PLANES FINALIZADOS - AUMENTO DE NIVEL **
        TipoNotificacion::create([ // ID: 6
            'imagen' => null,
        ]);





        // ** INSIGNIA RACHA DIA LECTURA **
        TipoNotificacion::create([ // ID: 7
            'imagen' => null,
        ]);


        // ** INSIGNIA RACHA DIA LECTURA - AUMENTO DE NIVEL **
        TipoNotificacion::create([ // ID: 8
            'imagen' => null,
        ]);




        // ** INSIGNIA PLANES FINALIZADOS EN GRUPOS **
        TipoNotificacion::create([ // ID: 9
            'imagen' => null,
        ]);


        // ** INSIGNIA PLANES FINALIZADOS EN GRUPOS - AUMENTO DE NIVEL **
        TipoNotificacion::create([ // ID: 10
            'imagen' => null,
        ]);




        // ** UN AMIGO TE ACABA DE ENVIAR SOLICITUD **
        TipoNotificacion::create([ // ID: 11
            'imagen' => null,
        ]);



        // ** UN AMIGO ACABA DE ACEPTAR TU SOLICITUD **
        TipoNotificacion::create([ // ID: 12
            'imagen' => null,
        ]);


        // ** XX ACABA DE UNIRTE A UN PLAN GRUPAL **
        TipoNotificacion::create([ // ID: 13
            'imagen' => null,
        ]);
    }
}
