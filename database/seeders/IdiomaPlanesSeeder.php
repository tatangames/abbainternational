<?php

namespace Database\Seeders;

use App\Models\IdiomaPlanes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IdiomaPlanesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IdiomaPlanes::create([
            'nombre' => 'Español',
        ]);

        IdiomaPlanes::create([
            'nombre' => 'Ingles',
        ]);
    }
}
