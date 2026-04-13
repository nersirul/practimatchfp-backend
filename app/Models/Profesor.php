<?php

/**
 * Modelo de Dominio - Profesor
 * 
 * Gestiona el acceso e información de los tutores de los centros educativos.
 * Los profesores se encargan de vigilar el transcurso de la FCT y de valorar
 * o poner la nota final (APTO/NO APTO) a los alumnos en prácticas.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Clase Profesor
 */
class Profesor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /** @var string */
    protected $table = 'profesores';
    /** @var string */
    protected $primaryKey = 'id_profesor';

    /** @var array<int, string> */
    protected $fillable = ['nombre', 'apellidos', 'email', 'password', 'telefono', 'departamento', 'id_centro'];
    
    /** @var array<int, string> */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    /**
     * Relación 1:N con Práctica.
     * 
     * Retorna todas las prácticas (asignaciones Alumno - Empresa) de las
     * que este profesor es supervisor/tutor.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practicas()
    {
        return $this->hasMany(Practica::class, 'id_profesor', 'id_profesor');
    }
}
