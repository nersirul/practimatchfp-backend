<?php

/**
 * Modelo Base - User
 * 
 * Modelo auto-generado por Laravel. En PractiMatch FP usamos principalmente
 * los modelos específicos de perfiles (Alumno, Profesor, Administrador, Empresa).
 * Se mantiene por compatibilidad y arquitecturas futuras.
 * 
 * @package App\Models
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Clase User
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Los atributos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Atributos ocultos (no exportables en JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Devuelve los atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
