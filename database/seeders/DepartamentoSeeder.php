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
        //** El Salvador */

        Departamentos::create([
            'nombre' => 'Santa Ana',
            'id_pais' => '1'
        ]);

        Departamentos::create([
            'nombre' => 'Metapan',
            'id_pais' => '1'
        ]);


        //** Guatemala */

        Departamentos::create([
            'nombre' => 'Chiquimula',
            'id_pais' => '2'
        ]);

        Departamentos::create([
            'nombre' => 'Jalapa',
            'id_pais' => '2'
        ]);




    }
}
