<?php

/**
 * Modelo de Dominio - Valoracion
 * 
 * Contiene la nota final o decisión del profesor (tutor) sobre la FCT de un alumno.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Clase Valoracion
 */
class Valoracion extends Model
{
    /** @var string */
    protected $table = 'valoraciones';
    
    /** @var string */
    protected $primaryKey = 'id_valoracion';
    
    /** @var array<int, string> */
    protected $fillable = ['id_practica', 'calificacion', 'nota_numerica', 'comentarios_profesor'];

    /**
     * Relación 1:1 (Inversa) con Práctica.
     * 
     * Retorna a qué Práctica específica corresponde esta valoración y evaluación.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practica()
    {
        return $this->belongsTo(Practica::class, 'id_practica', 'id_practica');
    }
}
