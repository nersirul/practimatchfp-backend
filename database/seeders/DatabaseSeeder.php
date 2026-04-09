<?php

/**
 * Seeder de Base de Datos - DatabaseSeeder
 * 
 * Clase central para poblar la base de datos con información inicial ("dummy data").
 * Genera perfiles de usuario, categorías, tecnologías base y establece el marco
 * de una oferta y práctica de prueba para propósitos de demostración y testing.
 * 
 * Se invoca mediante: php artisan db:seed
 * 
 * @package Database\Seeders
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Administrador;
use App\Models\Empresa;
use App\Models\Alumno;
use App\Models\Categoria;
use App\Models\Tecnologia;
use App\Models\Oferta;
use App\Models\Profesor;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta las semillas en la base de datos.
     * 
     * El orden de inserción es crítico para no infligir restricciones de llaves foráneas.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Crear usuario Administrador (Super Admin)
        Administrador::create([
            'nombre' => 'Super Admin',
            'email' => 'admin@practimatch.com',
            'password' => Hash::make('password'), 
        ]);

        // 2. Crear Diccionario Básico de Categorías y Tecnologías
        $catWeb = Categoria::create(['nombre' => 'Desarrollo Web']);
        $catSys = Categoria::create(['nombre' => 'Sistemas']);

        $php = Tecnologia::create(['nombre' => 'PHP']);
        $js = Tecnologia::create(['nombre' => 'JavaScript']);
        $linux = Tecnologia::create(['nombre' => 'Linux']);

        // Vinculaciones pivot: Unir tecnologías a sus respectivas categorías
        $catWeb->tecnologias()->attach([$php->id_tecnologia, $js->id_tecnologia]);
        $catSys->tecnologias()->attach($linux->id_tecnologia);

        // 3. Crear Perfil Empresa pre-activado
        $empresa1 = Empresa::create([
            'nombre_comercial' => 'Tech Solutions',
            'cif' => 'B12345678',
            'email_contacto' => 'rrhh@tech.com',
            'password' => Hash::make('password'),
            'sector' => 'Consultoría',
            'descripcion' => 'Empresa líder en desarrollo web.',
            'telefono_contacto' => '911222333',
            'direccion' => 'Av. Tecnológica 5',
            'ciudad' => 'Madrid',
        ]);
        // Forzamos que la empresa esté activa simulando que el admin la validó
        $empresa1->update(['activa' => true]);

        // 4. Crear Perfil de Alumno
        $alumno1 = Alumno::create([
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'nif' => '12345678A',
            'email' => 'juan@alumno.com',
            'password' => Hash::make('password'),
            'ciclo' => 'DAW',
            'modalidad_preferida' => 'HIBRIDO',
            'telefono' => '655444333',
            'direccion' => 'Calle Principal 1',
            'ciudad' => 'Madrid',
        ]);

        // Inyectar conocimientos en el perfil de "Juan"
        $alumno1->tecnologias()->attach($php->id_tecnologia, ['tipo_relacion' => 'CONOCE', 'nivel' => 8]);

        // 5. Instanciar una Oferta Pública para el buscador
        $oferta = Oferta::create([
            'id_empresa' => $empresa1->id_empresa,
            'id_admin_validador' => 1, 
            'titulo' => 'Desarrollador Junior Laravel',
            'descripcion' => 'Buscamos gente con ganas.',
            'modalidad' => 'REMOTO',
            'estado' => 'PUBLICADA'
        ]);

        // Declarar requisitos para esta oferta
        $oferta->tecnologias()->attach($php->id_tecnologia);

        // 6. Crear un Tutor / Supervisor
        Profesor::create([
            'nombre' => 'Marta',
            'apellidos' => 'Tutor',
            'email' => 'marta@instituto.com',
            'password' => Hash::make('password'),
            'telefono' => '600111222',
            'departamento' => 'Informática'
        ]);
    }
}
