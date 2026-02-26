<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seguimiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos';
    protected $primaryKey = 'id_seguimiento';

    protected $fillable = [
        'id_practica',
        'fecha_envio',
        'tipo',
        'respuestas_json',
        'completado',
    ];

    protected $casts = [
        'respuestas_json' => 'array', // Convierte automáticamente el JSON a Array PHP
        'completado' => 'boolean',
        'fecha_envio' => 'datetime',
    ];

    public function practica()
    {
        return $this->belongsTo(Practica::class, 'id_practica', 'id_practica');
    }
}
