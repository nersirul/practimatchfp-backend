<?php

/**
 * Modelo de Dominio - Administrador
 * 
 * Gestiona los datos y comportamientos del administrador de la plataforma.
 * Esta clase hereda de Authenticatable para poder emitir tokens de sesión (Sanctum) y autenticar usuarios.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Clase Administrador
 * 
 * Representa al administrador del SaaS con privilegios para validar empresas y ofertas.
 * Implementa borrado lógico (SoftDeletes) para mantener la integridad histórica en la BD.
 */
class Administrador extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Nombre de la tabla en base de datos.
     * @var string
     */
    protected $table = 'administradores';

    /**
     * Clave primaria personalizada.
     * @var string
     */
    protected $primaryKey = 'id_admin';

    /**
     * Atributos que se pueden asignar masivamente (Mass Assignment).
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
    ];

    /**
     * Atributos ocultos cuando el modelo se serializa a JSON.
     * Protege datos sensibles como contraseñas o tokens.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define los casteos (conversiones) de atributos.
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relación 1:N con Oferta.
     * 
     * Retorna las ofertas que han sido validadas por este administrador en específico.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ofertasValidales()
    {
        return $this->hasMany(Oferta::class, 'id_admin_validador', 'id_admin');
    }
}
