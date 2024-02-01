<?php

namespace Database\Seeders;

use App\Models\IdiomaSistema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TextoIdiomaSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IdiomaSistema::create([
            'espanol' => 'Recuperación de Contraseña',
            'ingles' => 'Password recovery',
        ]);

        IdiomaSistema::create([
            'espanol' => 'Hola',
            'ingles' => 'Hello',
        ]);

        IdiomaSistema::create([
            'espanol' => 'Se ha solicitado un código de recuperación de contraseña',
            'ingles' => 'A password recovery code has been requested',
        ]);

        IdiomaSistema::create([
            'espanol' => 'Su código de recuperación es',
            'ingles' => 'Your recovery code is',
        ]);

        IdiomaSistema::create([
            'espanol' => 'Si usted no realizar esta solicitud, puede ignorar este mensaje',
            'ingles' => 'If you do not make this request, you can ignore this message',
        ]);
    }
}
