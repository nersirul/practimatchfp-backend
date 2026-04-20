<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent - Centro
 * 
 * Representa la entidad institucional (Centro Educativo/Instituto de Formación Profesional).
 * Centraliza la relación académica agrupando a múltiples alumnos y profesores bajo una misma entidad certificadora.
 * 
 * @package App\Models
 */
class Centro extends Model
{
    protected $primaryKey = 'id_centro';
    protected $fillable = ['nombre'];
}
