<?php

namespace Database\Seeders;

use App\Models\Iglesias;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IglesiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //** El Salvador -> Santa Ana */

        Iglesias::create([
            'nombre' => 'iglesia santa ana 1',
            'id_departamento' => '1'
        ]);

        Iglesias::create([
            'nombre' => 'iglesia santa ana 2',
            'id_departamento' => '1'
        ]);

        //** El Salvador -> metapan */


        Iglesias::create([
            'nombre' => 'iglesia metapan 3',
            'id_departamento' => '2'
        ]);

        Iglesias::create([
            'nombre' => 'iglesia metapan 4',
            'id_departamento' => '2'
        ]);

        Iglesias::create([
            'nombre' => 'iglesia metapan 5',
            'id_departamento' => '2'
        ]);


        //** Guatemala -> chiquimula */

        Iglesias::create([
            'nombre' => 'iglesia chiquimula 6',
            'id_departamento' => '3'
        ]);

        Iglesias::create([
            'nombre' => 'iglesia chiquimula 7',
            'id_departamento' => '3'
        ]);

        //** Guatemala -> jalapa */

        Iglesias::create([
            'nombre' => 'iglesia jalapa 8',
            'id_departamento' => '4'
        ]);

        Iglesias::create([
            'nombre' => 'iglesia jalapa 9',
            'id_departamento' => '4'
        ]);

        Iglesias::create([
            'nombre' => 'iglesia jalapa 10',
            'id_departamento' => '4'
        ]);

    }
}
