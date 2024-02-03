<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoVideoSeeder::create([
            'nombre' => 'Facebook',
        ]);

        TipoVideoSeeder::create([
            'nombre' => 'Instagram',
        ]);

        TipoVideoSeeder::create([
            'nombre' => 'Youtube',
        ]);
    }
}
