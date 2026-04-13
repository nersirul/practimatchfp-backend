<?php

/**
 * Modelo de Dominio - Alumno
 * 
 * Este modelo gestiona la información de los estudiantes que buscan prácticas.
 * Hereda de Authenticatable para el acceso mediante Sanctum.
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Models\Centro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Clase Alumno
 * 
 * Representa a un estudiante. Un alumno pertenece a un ciclo formativo
 * y puede establecer múltiples candidaturas (Prácticas) a distintas ofertas
 * y definir sus Tecnologías aprendidas.
 */
class Alumno extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Nombre de la tabla en base de datos asociada a este modelo.
     * @var string
     */
    protected $table = 'alumnos';

    /**
     * Clave primaria personalizada.
     * @var string
     */
    protected $primaryKey = 'id_alumno';

    /**
     * Atributos que son asignables de forma masiva.
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'nif',
        'email',
        'password',
        'ciclo',
        'modalidad_preferida',
        'telefono',
        'direccion',
        'ciudad',
        'id_centro',
        'id_profesor',
    ];

    /**
     * Atributos ocultos para arrays y JSON (protege credenciales).
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mapeo de tipos de atributos.
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relación N:M con Tecnología.
     * 
     * Resuelve las tecnologías o conocimientos que el alumno declara dominar.
     * Se cargan atributos extra (pivot) de la tabla intermedia `alumno_tecnologia`
     * para saber si le gusta o su nivel de experiencia.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tecnologias()
    {
        return $this->belongsToMany(Tecnologia::class, 'alumno_tecnologia', 'id_alumno', 'id_tecnologia')
            ->withPivot('tipo_relacion', 'nivel')
            ->withTimestamps(); // Incluimos marcas de tiempo para la tabla pivote
    }

    /**
     * Relación 1:N con Practica (Candidaturas).
     * 
     * Obtiene el historial de todas las prácticas/ofertas a las que el alumno ha aplicado
     * y su estado actual.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practicas()
    {
        return $this->hasMany(Practica::class, 'id_alumno', 'id_alumno');
    }

    // Relación con el Centro Educativo
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'id_centro', 'id_centro');
    }

    // Relación con el Profesor (Tutor)
    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }
}
