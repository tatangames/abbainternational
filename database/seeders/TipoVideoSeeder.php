<?php

namespace Database\Seeders;

use App\Models\TipoVideo;
use Illuminate\Database\Seeder;

class TipoVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoVideo::create([
            'nombre' => 'Facebook',
        ]);

        TipoVideo::create([
            'nombre' => 'Instagram',
        ]);

        TipoVideo::create([
            'nombre' => 'Youtube',
        ]);
    }
}
