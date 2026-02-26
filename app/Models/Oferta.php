<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'ofertas';
    protected $primaryKey = 'id_oferta';

    protected $fillable = [
        'id_empresa',
        'id_admin_validador',
        'titulo',
        'descripcion',
        'modalidad',
        'es_remunerada',
        'posibilidad_contratacion',
        'estado',
    ];

    protected $casts = [
        'es_remunerada' => 'boolean',
        'posibilidad_contratacion' => 'boolean',
    ];

    // Relación: Pertenece a una Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    // Relación: Validada por un Admin
    public function validador()
    {
        return $this->belongsTo(Administrador::class, 'id_admin_validador', 'id_admin');
    }

    // Relación N:M con Tecnologías (Requisitos)
    public function tecnologias()
    {
        return $this->belongsToMany(Tecnologia::class, 'oferta_tecnologia', 'id_oferta', 'id_tecnologia');
    }

    // Relación: Genera Prácticas
    public function practicas()
    {
        return $this->hasMany(Practica::class, 'id_oferta', 'id_oferta');
    }
}
