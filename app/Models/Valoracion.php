<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valoracion extends Model
{
    protected $table = 'valoraciones';
    protected $primaryKey = 'id_valoracion';
    protected $fillable = ['id_practica', 'calificacion', 'nota_numerica', 'comentarios_profesor'];

    public function practica()
    {
        return $this->belongsTo(Practica::class, 'id_practica', 'id_practica');
    }
}
