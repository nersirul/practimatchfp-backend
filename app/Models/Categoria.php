<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';

    protected $fillable = ['nombre'];

    // Relación N:M
    public function tecnologias()
    {
        return $this->belongsToMany(Tecnologia::class, 'tecnologia_categoria', 'id_categoria', 'id_tecnologia');
    }
}
