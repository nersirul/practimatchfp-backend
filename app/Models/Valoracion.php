<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Practica;

class Valoracion extends Model
{
    use HasFactory;

    protected $table = 'valoraciones';
    protected $primaryKey = 'id_valoracion';

    protected $fillable = [
        'id_practica',
        'puntuacion',
        'comentario',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function practica()
    {
        return $this->belongsTo(Practica::class, 'id_practica', 'id_practica');
    }
}
