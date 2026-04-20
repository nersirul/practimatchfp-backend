<?php

/**
 * Seeder de Base de Datos - DatabaseSeeder
 * * Clase central para poblar la base de datos con información masiva ("dummy data") para pruebas y demostraciones.
 * Genera entidades clave: alumnos, empresas, profesores, centros, tecnologías y ofertas.
 * * Se invoca mediante: php artisan db:seed
 * * @package Database\Seeders
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
use App\Models\Centro;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta las semillas en la base de datos.
     * * El orden de inserción es crítico para no infligir restricciones de llaves foráneas.
     *
     * @return void
     */
    public function run(): void
    {
        // Instanciar Faker en español para generar datos realistas
        $faker = Faker::create('es_ES');
        $password = Hash::make('password'); // Misma contraseña para todos según requerimientos

        // 1. Crear usuario Administrador (Super Admin)
        Administrador::create([
            'nombre' => 'Super Admin',
            'email' => 'admin@practimatch.com',
            'password' => $password,
        ]);

        // 2. Crear Diccionario Amplio de Categorías y Tecnologías
        $categoriasData = [
            'Desarrollo de Software' => ['PHP', 'JavaScript', 'Python', 'Java', 'C#', 'C++', 'Ruby', 'Swift', 'Kotlin', 'Go', 'React', 'Angular', 'Vue.js', 'Node.js', 'Laravel', 'Spring Boot', '.NET'],
            'Sistemas y Redes' => ['Linux', 'Windows Server', 'Cisco', 'Active Directory', 'Proxmox', 'VMware', 'Bash', 'PowerShell', 'Nginx', 'Apache'],
            'Cloud Computing' => ['AWS', 'Google Cloud', 'Microsoft Azure', 'Docker', 'Kubernetes', 'Terraform', 'Ansible'],
            'Ciberseguridad' => ['Kali Linux', 'Wireshark', 'Metasploit', 'Nmap', 'Burp Suite', 'Snort', 'Security+'],
            'Big Data & IA' => ['Hadoop', 'Spark', 'TensorFlow', 'PyTorch', 'PowerBI', 'Tableau', 'Pandas', 'MongoDB', 'Redis', 'Elasticsearch']
        ];
        
        $todasTecnologias = []; // Guardamos referencia para asignar aleatoriamente después

        foreach ($categoriasData as $catName => $techs) {
            $categoria = Categoria::create(['nombre' => $catName]);
            foreach ($techs as $techName) {
                $tech = Tecnologia::create(['nombre' => $techName]);
                $todasTecnologias[] = $tech;
                // Vincular tecnología a categoría
                $categoria->tecnologias()->attach($tech->id_tecnologia);
            }
        }

        // 3. Entidades institucionales (Centros Educativos)
        $centros = [];
        $numCentros = $faker->numberBetween(20, 25);
        for ($i = 0; $i < $numCentros; $i++) {
            $centros[] = Centro::create([
                'nombre' => 'IES ' . $faker->unique()->company
            ]);
        }

        // 4. Crear 30 Profesores (Tutores de centro)
        $profesores = [];
        for ($i = 0; $i < 30; $i++) {
            $centroAleatorio = $faker->randomElement($centros);
            $profesores[] = Profesor::create([
                'id_centro' => $centroAleatorio->id_centro,
                'nombre' => $faker->firstName,
                'apellidos' => $faker->lastName . ' ' . $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => $password,
                'telefono' => $faker->numerify('#########'),
                'departamento' => $faker->randomElement(['Informática', 'Comunicaciones', 'Sistemas y Redes', 'Desarrollo Web'])
            ]);
        }

        // Agrupación de recursos humanos por entidad académica (Indexación)
        $profesoresPorCentro = [];
        foreach ($profesores as $prof) {
            $profesoresPorCentro[$prof->id_centro][] = $prof;
        }

        // 5. Crear 150 Alumnos
        $numAlumnos = 150;
        for ($i = 0; $i < $numAlumnos; $i++) {
            $centroAleatorio = $faker->randomElement($centros);
            
            // Resolución de relaciones (Centro -> Tutor)
            $profesorId = null;
            if (isset($profesoresPorCentro[$centroAleatorio->id_centro])) {
                $profesorAleatorio = $faker->randomElement($profesoresPorCentro[$centroAleatorio->id_centro]);
                // Distribución aleatoria de supervisiones (80% tasa de cobertura)
                $profesorId = $faker->boolean(80) ? $profesorAleatorio->id_profesor : null;
            }

            // Generar DNI o NIE realista
            $nif = $faker->unique()->numerify('########') . $faker->randomElement(['A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','V','W','X','Y','Z']);

            $alumno = Alumno::create([
                'id_centro' => $centroAleatorio->id_centro,
                'id_profesor' => $profesorId, 
                'nombre' => $faker->firstName,
                'apellidos' => $faker->lastName . ' ' . $faker->lastName,
                'nif' => $nif,
                'email' => $faker->unique()->safeEmail,
                'password' => $password,
                'ciclo' => $faker->randomElement(['DAW', 'DAM', 'ASIR', 'SMR']),
                'modalidad_preferida' => $faker->randomElement(['PRESENCIAL', 'REMOTO', 'HIBRIDO']),
                'telefono' => $faker->numerify('#########'),
                'direccion' => $faker->streetAddress,
                'ciudad' => $faker->city,
            ]);

            // Inyectar conocimientos aleatorios (entre 2 y 6 tecnologías por alumno)
            $techsSeleccionadas = $faker->randomElements($todasTecnologias, $faker->numberBetween(2, 6));
            foreach ($techsSeleccionadas as $tech) {
                $alumno->tecnologias()->attach($tech->id_tecnologia, [
                    'tipo_relacion' => $faker->randomElement(['CONOCE', 'INTERES']),
                    'nivel' => $faker->numberBetween(1, 10)
                ]);
            }
        }

        // 6. Crear 50 Empresas
        for ($i = 0; $i < 50; $i++) {
            $cif = 'B' . $faker->unique()->numerify('########');
            
            $empresa = Empresa::create([
                'nombre_comercial' => $faker->unique()->company,
                'cif' => $cif,
                'email_contacto' => $faker->unique()->companyEmail,
                'password' => $password,
                'sector' => $faker->randomElement(['Consultoría Tecnológica', 'Desarrollo de Software', 'Ciberseguridad', 'Innovación IT', 'Banca y Fintech', 'Educación IT', 'Agencia Digital']),
                'descripcion' => $faker->paragraph(3),
                'telefono_contacto' => $faker->numerify('#########'),
                'direccion' => $faker->streetAddress,
                'ciudad' => $faker->city,
                'activa' => $faker->boolean(95), // 95% están activas/validadas por admin
            ]);

            // Crear entre 1 y 10 Ofertas por empresa
            $numOfertas = $faker->numberBetween(1, 10);
            for ($o = 0; $o < $numOfertas; $o++) {
                // Generar ofertas mayoritariamente publicadas
                $estadoOferta = $faker->randomElement(['PUBLICADA', 'PUBLICADA', 'PUBLICADA', 'PENDIENTE', 'CERRADA']);

                $oferta = Oferta::create([
                    'id_empresa' => $empresa->id_empresa,
                    'id_admin_validador' => 1, // Admin 1
                    'titulo' => $faker->randomElement(['Desarrollador Junior ', 'Técnico de Sistemas ', 'Prácticas en Ciberseguridad ', 'Beca Consultoría ', 'Analista Junior de Datos ']) . $faker->randomElement(['', '(Remoto)', '- Urgente']),
                    'descripcion' => $faker->paragraph(4),
                    'modalidad' => $faker->randomElement(['REMOTO', 'PRESENCIAL', 'HIBRIDO']),
                    'es_remunerada' => $faker->boolean(40), // 40% son remuneradas
                    'posibilidad_contratacion' => $faker->boolean(75), // 75% tienen posibilidad real de contratación
                    'estado' => $estadoOferta,
                    'vacantes' => $faker->numberBetween(1, 5),
                    'activa' => true // la oferta por defecto activa
                ]);

                // Asignar requisitos (tecnologías) para la oferta
                $techsOferta = $faker->randomElements($todasTecnologias, $faker->numberBetween(1, 4));
                $idsParaAttach = array_map(fn($t) => $t->id_tecnologia, $techsOferta);
                $oferta->tecnologias()->attach($idsParaAttach);
            }
        }
    }
}
