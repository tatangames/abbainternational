<?php

namespace Database\Seeders;

use App\Models\Departamentos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //************** El Salvador *****************/


        Departamentos::create([
            'nombre' => 'Santa Ana', // ID: 1
            'id_pais' => '1',
            'id_zona_horaria' => '1'
        ]);

        Departamentos::create([
            'nombre' => 'Chalatenango', // ID: 2
            'id_pais' => '1',
            'id_zona_horaria' => '1'
        ]);

        Departamentos::create([
            'nombre' => 'Sonsonate', // ID: 3
            'id_pais' => '1',
            'id_zona_horaria' => '1'
        ]);

        Departamentos::create([
            'nombre' => 'La Libertad', // ID: 4
            'id_pais' => '1',
            'id_zona_horaria' => '1'
        ]);


        Departamentos::create([
            'nombre' => 'Ahuachapan', // ID: 5
            'id_pais' => '1',
            'id_zona_horaria' => '1'
        ]);



        //************** Guatemala *****************/


        Departamentos::create([
            'nombre' => 'San Marcos', // ID: 6
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Quetzaltenango', // ID: 7
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Retalhuleu', // ID: 8
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Suchitepéquez', // ID: 9
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);


        Departamentos::create([
            'nombre' => 'Sololá', // ID: 10
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Sacatepéquez', // ID: 11
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Chimaltenango', // ID: 12
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);


        Departamentos::create([
            'nombre' => 'Guatemala', // ID: 13
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Escuintla', // ID: 14
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Santa Rosa', // ID: 15
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Jalapa', // ID: 16
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);


        Departamentos::create([
            'nombre' => 'Jutiapa', // ID: 17
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Chiquimula', // ID: 18
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Zacapa', // ID: 19
            'id_pais' => '2',
            'id_zona_horaria' => '2'
        ]);



        //************** Honduras *****************/


        Departamentos::create([
            'nombre' => 'Francisco Morazán', // ID: 20
            'id_pais' => '3',
            'id_zona_horaria' => '3'
        ]);

        Departamentos::create([
            'nombre' => 'Olancho', // ID: 21
            'id_pais' => '3',
            'id_zona_horaria' => '3'
        ]);

        Departamentos::create([
            'nombre' => 'El Paraíso', // ID: 22
            'id_pais' => '3',
            'id_zona_horaria' => '3'
        ]);




        //************** Nicaragua *****************/


        Departamentos::create([
            'nombre' => 'Estelí', // ID: 23
            'id_pais' => '4',
            'id_zona_horaria' => '4'
        ]);


        Departamentos::create([
            'nombre' => 'Madriz', // ID: 24
            'id_pais' => '4',
            'id_zona_horaria' => '4'
        ]);

        Departamentos::create([
            'nombre' => 'Nueva Segovia', // ID: 25
            'id_pais' => '4',
            'id_zona_horaria' => '4'
        ]);



        //************** Mexico *****************/


        Departamentos::create([
            'nombre' => 'Hidalgo', // ID: 26
            'id_pais' => '5',
            'id_zona_horaria' => '5'
        ]);

        Departamentos::create([
            'nombre' => 'Chiapas', // ID: 27
            'id_pais' => '5',
            'id_zona_horaria' => '5'
        ]);




    }
}
