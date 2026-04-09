<?php

/**
 * Modelo de Dominio - Empresa
 * 
 * Este modelo define a las entidades (empresas) que ofrecen puestos de prácticas para alumnos.
 * Las empresas pueden publicar ofertas y aceptar o rechazar solicitudes de alumnos.
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
 * Clase Empresa
 * 
 * Representa una empresa dentro del SaaS. Una empresa recién creada tiene que ser
 * validada (activada) por un Administrador antes de que sus ofertas se publiquen.
 */
class Empresa extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Nombre de la tabla correspondiente en la base de datos.
     * @var string
     */
    protected $table = 'empresas';

    /**
     * Clave primaria personalizada.
     * @var string
     */
    protected $primaryKey = 'id_empresa';

    /**
     * Los atributos que son asignables en masa.
     * 
     * Nota: 'activa' controla si un administrador ya la ha validado o no.
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre_comercial',
        'cif',
        'direccion',
        'ciudad',
        'num_trabajadores',
        'sector',
        'email_contacto',
        'password',
        'descripcion',
        'telefono_contacto',
        'activa', 
    ];

    /**
     * Atributos ocultos en serializaciones de la entidad.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Configuración del casteo (casting) automático de tipos.
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
     * Devuelve todas las ofertas (publicadas o no) que ha creado esta empresa.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_empresa', 'id_empresa');
    }
}
