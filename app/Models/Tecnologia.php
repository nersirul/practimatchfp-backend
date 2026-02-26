<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tecnologia extends Model
{
    use HasFactory;

    protected $table = 'tecnologias';
    protected $primaryKey = 'id_tecnologia';

    protected $fillable = ['nombre'];

    // Relación N:M con Categorias
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'tecnologia_categoria', 'id_tecnologia', 'id_categoria');
    }

    // Relación N:M con Ofertas
    public function ofertas()
    {
        return $this->belongsToMany(Oferta::class, 'oferta_tecnologia', 'id_tecnologia', 'id_oferta');
    }

    // Relación N:M con Alumnos
    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_tecnologia', 'id_tecnologia', 'id_alumno')
            ->withPivot('tipo_relacion', 'nivel');
    }
}
