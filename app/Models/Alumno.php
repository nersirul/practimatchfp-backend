<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Alumno extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'alumnos';
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'nombre',
        'apellidos',
        'nif',
        'email',
        'password',
        'ciclo',
        'modalidad_preferida',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relación N:M con Tecnología (con atributos extra en la tabla pivote)
    public function tecnologias()
    {
        return $this->belongsToMany(Tecnologia::class, 'alumno_tecnologia', 'id_alumno', 'id_tecnologia')
            ->withPivot('tipo_relacion', 'nivel')
            ->withTimestamps(); // Si hubiéramos puesto timestamps en la pivote
    }

    // Relación: Un alumno realiza muchas prácticas (histórico)
    public function practicas()
    {
        return $this->hasMany(Practica::class, 'id_alumno', 'id_alumno');
    }
}
