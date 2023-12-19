<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmin = Role::create(['name' => 'Admin']);

        // Inventario
        $roleEditor = Role::create(['name' => 'Editor']);


        // solo para administrador
        Permission::create(['name' => 'sidebar.roles.y.permisos', 'description' => 'Sidebar seccion roles y permisos'])->syncRoles($roleAdmin);
        Permission::create(['name' => 'sidebar.dashboard', 'description' => 'Sidebar dashboard'])->syncRoles($roleEditor);



    }
}
