<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdministradorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Administrador::create([
            'nombre' => 'Jonathan',
            'usuario' => 'jonathan',
            'password' => bcrypt('1234'),
        ])->assignRole('Admin');
    }
}
