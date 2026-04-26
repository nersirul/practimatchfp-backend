<?php

/**
 * Modelo de Dominio - Practica
 * 
 * Este modelo es central en la plataforma. Representa una CANDIDATURA de un Alumno 
 * hacia una Oferta. Con el paso del tiempo, sus estados evolucionan ("SOLICITADA", "EN_CURSO", "FINALIZADA").
 * Por lo tanto, funge como un "Match" entre empresa y estudiante.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Clase Practica
 */
class Practica extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'practicas';

    /** @var string */
    protected $primaryKey = 'id_practica';

    /** @var array<int, string> */
    protected $fillable = [
        'id_oferta',
        'id_alumno',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'puntuacion_empresa',
        'comentario_alumno',
        'id_profesor',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Relación 1:N (Inversa) con Oferta.
     * 
     * Retorna la vacante (oferta) asociada a esta candidatura.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }

    /**
     * Relación 1:N (Inversa) con Alumno.
     * 
     * Retorna el estudiante que ha aplicado y está cursando la práctica.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    /**
     * Relación 1:N con Seguimiento.
     * 
     * El historial de partes enviados periódicamente por el alumno.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'id_practica', 'id_practica');
    }

    /**
     * Relación 1:1 con Valoracion.
     * 
     * Cuando la práctica acaba, el profesor asigna una única calificación/nota.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function valoracion()
    {
        return $this->hasOne(Valoracion::class, 'id_practica', 'id_practica');
    }

    /**
     * Relación 1:N (Inversa) con Profesor.
     * 
     * Retorna el tutor de centro asignado a vigilar (supervisar) a este alumno en particular.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }
}
