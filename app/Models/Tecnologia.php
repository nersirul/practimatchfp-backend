<?php

/**
 * Modelo de Dominio - Tecnologia
 * 
 * Diccionario global de lenguajes de programación, frameworks y herramientas.
 * Se utilizan para que los alumnos muestren sus habilidades y las empresas
 * establezcan sus requisitos en las ofertas.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Clase Tecnologia
 */
class Tecnologia extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'tecnologias';

    /** @var string */
    protected $primaryKey = 'id_tecnologia';

    /** @var array<int, string> */
    protected $fillable = ['nombre'];

    /**
     * Relación N:M con Categoria.
     * 
     * Retorna las categorías lógicas a las que esta tecnología pertenece (ej: Backend, Frontend).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'tecnologia_categoria', 'id_tecnologia', 'id_categoria');
    }

    /**
     * Relación N:M con Oferta.
     * 
     * Ofertas que exigen conocer esta tecnología como requisito.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ofertas()
    {
        return $this->belongsToMany(Oferta::class, 'oferta_tecnologia', 'id_tecnologia', 'id_oferta');
    }

    /**
     * Relación N:M con Alumno.
     * 
     * Alumnos que han declarado tener experiencia en esta tecnología.
     * Carga el estado extra "tipo_relacion" (ej: EXPERIENCIA, APRENDIENDO) y "nivel".
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_tecnologia', 'id_tecnologia', 'id_alumno')
            ->withPivot('tipo_relacion', 'nivel');
    }
}
