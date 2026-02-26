<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Administrador;
use App\Models\Empresa;
use App\Models\Alumno;
use App\Models\Categoria;
use App\Models\Tecnologia;
use App\Models\Oferta;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Administradores
        Administrador::create([
            'nombre' => 'Super Admin',
            'email' => 'admin@practimatch.com',
            'password' => Hash::make('password'), // La contraseña es 'password'
        ]);

        // 2. Crear Categorias y Tecnologías
        $catWeb = Categoria::create(['nombre' => 'Desarrollo Web']);
        $catSys = Categoria::create(['nombre' => 'Sistemas']);

        $php = Tecnologia::create(['nombre' => 'PHP']);
        $js = Tecnologia::create(['nombre' => 'JavaScript']);
        $linux = Tecnologia::create(['nombre' => 'Linux']);

        // Relacionar (Pivot)
        $catWeb->tecnologias()->attach([$php->id_tecnologia, $js->id_tecnologia]);
        $catSys->tecnologias()->attach($linux->id_tecnologia);

        // 3. Crear Empresas
        $empresa1 = Empresa::create([
            'nombre_comercial' => 'Tech Solutions',
            'cif' => 'B12345678',
            'email_contacto' => 'rrhh@tech.com',
            'password' => Hash::make('password'),
            'sector' => 'Consultoría',
            'descripcion' => 'Empresa líder en desarrollo web.'
        ]);

        // 4. Crear Alumnos
        $alumno1 = Alumno::create([
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'nif' => '12345678A',
            'email' => 'juan@alumno.com',
            'password' => Hash::make('password'),
            'ciclo' => 'DAW',
            'modalidad_preferida' => 'HIBRIDO'
        ]);

        // Asignar tecnología al alumno (Sabe PHP nivel 8)
        $alumno1->tecnologias()->attach($php->id_tecnologia, ['tipo_relacion' => 'CONOCE', 'nivel' => 8]);

        // 5. Crear Oferta
        $oferta = Oferta::create([
            'id_empresa' => $empresa1->id_empresa,
            'id_admin_validador' => 1, // Validada por el admin 1
            'titulo' => 'Desarrollador Junior Laravel',
            'descripcion' => 'Buscamos gente con ganas.',
            'modalidad' => 'REMOTO',
            'estado' => 'PUBLICADA'
        ]);

        // La oferta requiere PHP
        $oferta->tecnologias()->attach($php->id_tecnologia);
    }
}
