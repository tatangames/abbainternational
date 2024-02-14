<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class);
        $this->call(AdministradorSeeder::class);
        $this->call(GenerosSeeder::class);
        $this->call(PaisSeeder::class);
        $this->call(ZonaHorariaSeed::class);
        $this->call(DepartamentoSeeder::class);
        $this->call(IglesiaSeeder::class);
        $this->call(TextoIdiomaSistemaSeeder::class);
        $this->call(IdiomaPlanesSeeder::class);
        $this->call(TipoVideoSeeder::class);

        $this->call(InsigniasSeeder::class); // 5 insignias
        $this->call(InsigniasTextosSeeder::class); // texto cada insignia
        $this->call(InsigniasNivelesSeeder::class); // niveles cada insignia
        $this->call(ComparteAppSeeder::class); // texto comparte app

        $this->call(TipoNotificacionSeeder::class);
        $this->call(NotificacionTextoSeeder::class);

    }
}
