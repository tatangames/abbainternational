<?php

namespace Database\Seeders;

use App\Models\ComparteApp;
use App\Models\ComparteAppTextos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComparteAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ComparteApp::create([
            'imagen' => 'xx',
        ]);

        ComparteAppTextos::create([
            'id_idioma_planes' => '1',
            'texto_1' => 'Comparte nuestra aplicación',
            'texto_2' => 'Entre mas compartas, mas oportunidad de ganar insignias tienes',
        ]);

        ComparteAppTextos::create([
            'id_idioma_planes' => '2',
            'texto_1' => 'Share our app',
            'texto_2' => 'The more you share, the more chance you have to earn badges',
        ]);
    }
}
