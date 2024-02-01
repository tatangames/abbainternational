<?php

namespace Database\Seeders;

use App\Models\Pais;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaisSeeder extends Seeder
{
    /**
     * AGREGAR PAISES
     */
    public function run(): void
    {
        Pais::create([
            'nombre' => 'El Salvador', // ID: 1
        ]);

        Pais::create([
            'nombre' => 'Guatemala', // ID: 2
        ]);

        Pais::create([
            'nombre' => 'Honduras', // ID: 3
        ]);

        Pais::create([
            'nombre' => 'Nicaragua', // ID: 4
        ]);

        Pais::create([
            'nombre' => 'Mexico', // ID: 5
        ]);
    }
}
