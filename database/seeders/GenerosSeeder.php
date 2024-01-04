<?php

namespace Database\Seeders;

use App\Models\Generos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenerosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Generos::create([
            'nombre' => 'Masculino',
        ]);

        Generos::create([
            'nombre' => 'Femenino',
        ]);
    }
}
