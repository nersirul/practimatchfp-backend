<?php

/**
 * Modelo de Dominio - Categoria
 * 
 * Usado para agrupar Tecnologías. Por ejemplo, la categoría "Bases de Datos"
 * podría contener MySQL, MongoDB, etc.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Clase Categoria
 */
class Categoria extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'categorias';
    
    /** @var string */
    protected $primaryKey = 'id_categoria';

    /** @var array<int, string> */
    protected $fillable = ['nombre'];

    /**
     * Relación N:M con Tecnología.
     * 
     * Retorna todas las tecnologías que pertenecen a esta categoría particular.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tecnologias()
    {
        return $this->belongsToMany(Tecnologia::class, 'tecnologia_categoria', 'id_categoria', 'id_tecnologia');
    }
}
