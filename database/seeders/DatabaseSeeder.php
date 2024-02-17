<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * SEMILLAS
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class); // roles
        $this->call(AdministradorSeeder::class); // usuarios administrador
        $this->call(GenerosSeeder::class); // 2 tipos de generos
        $this->call(PaisSeeder::class); // paises
        $this->call(ZonaHorariaSeed::class); // zonas horarias para el pais
        $this->call(DepartamentoSeeder::class); // departamentos para x pais
        $this->call(IglesiaSeeder::class); // iglesias para x departamentos
        $this->call(TextoIdiomaSistemaSeeder::class); // idioma para textos de sistema, ejemplo recuperar password
        $this->call(IdiomaPlanesSeeder::class); // diferentes idiomas lectura de todos los textos
        $this->call(TipoVideoSeeder::class); // tipos de video para cada url

        $this->call(InsigniasSeeder::class); // 5 insignias
        $this->call(InsigniasTextosSeeder::class); // texto cada insignia
        $this->call(InsigniasNivelesSeeder::class); // niveles cada insignia
        $this->call(ComparteAppSeeder::class); // texto comparte app

        $this->call(TipoNotificacionSeeder::class); // tipo notificacion para usuario
        $this->call(NotificacionTextoSeeder::class); // textos de las notificaciones

    }
}
