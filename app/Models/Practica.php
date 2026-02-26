<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Practica extends Model
{
    use HasFactory;

    protected $table = 'practicas';
    protected $primaryKey = 'id_practica';

    protected $fillable = [
        'id_oferta',
        'id_alumno',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // Pertenece a una Oferta
    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }

    // Pertenece a un Alumno
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    // Tiene muchos seguimientos
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'id_practica', 'id_practica');
    }

    // Tiene una valoración final
    public function valoracion()
    {
        return $this->hasOne(Valoracion::class, 'id_practica', 'id_practica');
    }
}
