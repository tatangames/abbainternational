<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministradorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Administrador::create([
            'nombre' => 'Jonathan',
            'password' => Hash::make('1234'),
            'email' => 'tatangamess@gmail.com',
            'token_correo' => null,
            'token_fecha' => null
        ])->assignRole('admin');

        Administrador::create([
            'nombre' => 'Editor',
            'password' => Hash::make('1234'),
            'email' => 'editor@gmail.com',
            'token_correo' => null,
            'token_fecha' => null
        ])->assignRole('editor');
    }
}
