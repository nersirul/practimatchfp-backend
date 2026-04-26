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
use App\Models\Practica;
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

        // 2. Crear Diccionario Amplio de Categorías y Tecnologías (MANTENIDO INTACTO)
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

        // =================================================================
        // 3. CREACIÓN DEL ESCENARIO DE PRUEBAS (TRIO RELACIONADO)
        // =================================================================

        // Centro de Pruebas
        $centroPruebas = Centro::create(['nombre' => 'Centro de Formación Especial']);

        // Profesor Especial
        $profesorEspecial = Profesor::create([
            'id_centro' => $centroPruebas->id_centro,
            'nombre' => 'Profesor',
            'apellidos' => 'Test',
            'email' => 'profesor@profesor.com',
            'password' => $password,
            'telefono' => '600000001',
            'departamento' => 'Informática'
        ]);

        // Alumno Especial (Vinculado al profesor y centro)
        $alumnoEspecial = Alumno::create([
            'id_centro' => $centroPruebas->id_centro,
            'id_profesor' => $profesorEspecial->id_profesor,
            'nombre' => 'Alumno',
            'apellidos' => 'Especial',
            'nif' => '12345678Z',
            'email' => 'alumno@alumno.com',
            'password' => $password,
            'ciclo' => 'DAW',
            'modalidad_preferida' => 'HIBRIDO',
            'telefono' => '600000002',
            'direccion' => 'Calle Principal 1',
            'ciudad' => 'Madrid',
        ]);

        // Inyectarle al alumno especial algunas tecnologías conocidas
        $alumnoEspecial->tecnologias()->attach($todasTecnologias[0]->id_tecnologia, ['nivel' => 8, 'tipo_relacion' => 'CONOCE']);
        $alumnoEspecial->tecnologias()->attach($todasTecnologias[1]->id_tecnologia, ['nivel' => 7, 'tipo_relacion' => 'CONOCE']);

        // Empresa Especial
        $empresaEspecial = Empresa::create([
            'nombre_comercial' => 'Empresa Especial S.A.',
            'cif' => 'B00000000',
            'email_contacto' => 'empresa@empresa.com',
            'password' => $password,
            'sector' => 'Desarrollo de Software',
            'descripcion' => 'Empresa creada para pruebas de integración del sistema.',
            'telefono_contacto' => '912345678',
            'direccion' => 'Avenida de la Tecnología 40',
            'ciudad' => 'Madrid',
            'activa' => true,
        ]);

        // Oferta para la Empresa Especial
        $ofertaEspecial = Oferta::create([
            'id_empresa' => $empresaEspecial->id_empresa,
            'id_admin_validador' => 1,
            'titulo' => 'Beca Fullstack Especial',
            'descripcion' => 'Oferta de prácticas diseñada para el alumno especial.',
            'modalidad' => 'HIBRIDO',
            'es_remunerada' => true,
            'posibilidad_contratacion' => true,
            'estado' => 'PUBLICADA',
            'vacantes' => 2,
            'activa' => true
        ]);
        $ofertaEspecial->tecnologias()->attach([$todasTecnologias[0]->id_tecnologia, $todasTecnologias[1]->id_tecnologia]);

        // Crear una PRÁCTICA FINALIZADA con VALORACIÓN para el Alumno Especial
        Practica::create([
            'id_alumno' => $alumnoEspecial->id_alumno,
            'id_oferta' => $ofertaEspecial->id_oferta,
            'id_profesor' => $profesorEspecial->id_profesor,
            'estado' => 'FINALIZADA',
            'puntuacion_empresa' => 5, // Valoración de 5 estrellas ya pre-cargada
            'comentario_alumno' => 'Una experiencia increíble, el ambiente de trabajo es fantástico y el tutor de la empresa me ayudó en todo.'
        ]);


        // =================================================================
        // 4. GENERACIÓN MASIVA (DUMMY DATA RESTANTE)
        // =================================================================

        // 3. Entidades institucionales (Centros Educativos adicionales)
        $centros = [$centroPruebas];
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
                $profesorId = $faker->boolean(80) ? $profesorAleatorio->id_profesor : null;
            }

            $nif = $faker->unique()->numerify('########') . $faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'Z']);

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
                $estadoOferta = $faker->randomElement(['PUBLICADA', 'PUBLICADA', 'PUBLICADA', 'PENDIENTE', 'CERRADA']);

                $oferta = Oferta::create([
                    'id_empresa' => $empresa->id_empresa,
                    'id_admin_validador' => 1, // Admin 1
                    'titulo' => $faker->randomElement(['Desarrollador Junior ', 'Técnico de Sistemas ', 'Prácticas en Ciberseguridad ', 'Beca Consultoría ', 'Analista Junior de Datos ']) . $faker->randomElement(['', '(Remoto)', '- Urgente']),
                    'descripcion' => $faker->paragraph(4),
                    'modalidad' => $faker->randomElement(['REMOTO', 'PRESENCIAL', 'HIBRIDO']),
                    'es_remunerada' => $faker->boolean(40),
                    'posibilidad_contratacion' => $faker->boolean(75),
                    'estado' => $estadoOferta,
                    'vacantes' => $faker->numberBetween(1, 5),
                    'activa' => true
                ]);

                // Asignar requisitos (tecnologías) para la oferta
                $techsOferta = $faker->randomElements($todasTecnologias, $faker->numberBetween(1, 4));
                $idsParaAttach = array_map(fn($t) => $t->id_tecnologia, $techsOferta);
                $oferta->tecnologias()->attach($idsParaAttach);

                // =================================================================
                // 7. PRÁCTICAS ALEATORIAS CON VALORACIONES
                // =================================================================
                // Si la oferta está cerrada o publicada, simulamos que algún alumno la cursó
                if ($faker->boolean(20)) {
                    $alumnoRandom = Alumno::inRandomOrder()->first();
                    $estadoPractica = $faker->randomElement(['SOLICITADA', 'EN_CURSO', 'FINALIZADA', 'RECHAZADA']);

                    $practica = Practica::create([
                        'id_alumno' => $alumnoRandom->id_alumno,
                        'id_oferta' => $oferta->id_oferta,
                        'id_profesor' => $alumnoRandom->id_profesor, // Puede ser null si no tiene tutor
                        'estado' => $estadoPractica,
                        // Solo valoramos si está finalizada
                        'puntuacion_empresa' => ($estadoPractica === 'FINALIZADA') ? $faker->numberBetween(1, 5) : null,
                        'comentario_alumno' => ($estadoPractica === 'FINALIZADA' && $faker->boolean(60)) ? $faker->sentence() : null
                    ]);
                }
            }
        }
    }
}
