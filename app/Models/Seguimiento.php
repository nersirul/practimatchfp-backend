<?php

/**
 * Modelo de Dominio - Seguimiento
 * 
 * Gestiona los reportes o cuadernos diarios/semanales que el alumno 
 * debe llenar como parte de la evaluación continua de su Práctica FCT.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Clase Seguimiento
 */
class Seguimiento extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'seguimientos';

    /** @var string */
    protected $primaryKey = 'id_seguimiento';

    /** @var array<int, string> */
    protected $fillable = [
        'id_practica',
        'fecha_envio',
        'tipo',
        'respuestas_json',
        'completado',
    ];

    /**
     * Mapeo de atributos dinámicos. Destaca el autodeserializado del JSON
     * a arreglo en memoria.
     * @var array<string, string>
     */
    protected $casts = [
        'respuestas_json' => 'array',
        'completado' => 'boolean',
        'fecha_envio' => 'datetime',
    ];

    /**
     * Relación 1:N (Inversa) con Práctica.
     * 
     * Retorna a qué Práctica (asignación) pertenece este parte de seguimiento.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practica()
    {
        return $this->belongsTo(Practica::class, 'id_practica', 'id_practica');
    }
}
