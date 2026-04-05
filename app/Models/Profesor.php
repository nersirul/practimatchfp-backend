<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Profesor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'profesores';
    protected $primaryKey = 'id_profesor';

    protected $fillable = ['nombre', 'apellidos', 'email', 'password', 'telefono', 'departamento'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    // Un profesor supervisa muchas prácticas
    public function practicas()
    {
        return $this->hasMany(Practica::class, 'id_profesor', 'id_profesor');
    }
}
