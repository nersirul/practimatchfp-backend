<?php

/**
 * Modelo de Dominio - Oferta
 * 
 * Las ofertas o vacantes de prácticas publicadas por las empresas.
 * Deben ser validadas por un administrador antes de mostrarse en el buscador del alumno.
 * 
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Clase Oferta
 */
class Oferta extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'ofertas';
    
    /** @var string */
    protected $primaryKey = 'id_oferta';

    /** @var array<int, string> */
    protected $fillable = [
        'id_empresa',
        'id_admin_validador',
        'titulo',
        'descripcion',
        'modalidad',
        'es_remunerada',
        'posibilidad_contratacion',
        'estado',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'es_remunerada' => 'boolean',
        'posibilidad_contratacion' => 'boolean',
    ];

    /**
     * Relación 1:N (Inversa) con Empresa.
     * 
     * Retorna la empresa propietaria de la oferta.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /**
     * Relación 1:N (Inversa) con Administrador.
     * 
     * Retorna, si lo hay, el administrador que ha revisado y validado la oferta.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function validador()
    {
        return $this->belongsTo(Administrador::class, 'id_admin_validador', 'id_admin');
    }

    /**
     * Relación N:M con Tecnología.
     * 
     * Las tecnologías o herramientas que se solicitan como requisitos para aplicar a la oferta.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tecnologias()
    {
        return $this->belongsToMany(Tecnologia::class, 'oferta_tecnologia', 'id_oferta', 'id_tecnologia');
    }

    /**
     * Relación 1:N con Práctica.
     * 
     * Todas las candidaturas y prácticas asignadas a esta oferta por parte de uno o varios alumnos.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practicas()
    {
        return $this->hasMany(Practica::class, 'id_oferta', 'id_oferta');
    }

    /**
     * Scope Local para búsqueda unificada.
     * 
     * Permite al frontend buscar dinámicamente ofertas aplicando filtros combinados:
     * modalidad (presencial, remoto...) y tecnologías específicas necesarias.
     * Solo retorna ofertas en estado 'PUBLICADA'.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Construtor de la Query actual
     * @param array $filtros Arreglo asociativo con claves 'modalidad' o 'tecnologias'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFiltros($query, $filtros)
    {
        // Aplicar filtro estricto por Modalidad (REMOTO, PRESENCIAL, HIBRIDO) si existe
        if (isset($filtros['modalidad']) && $filtros['modalidad'] !== '') {
            $query->where('modalidad', $filtros['modalidad']);
        }

        // Aplicar filtro relacional por conjunto de Tecnologías solicitadas
        if (isset($filtros['tecnologias']) && is_array($filtros['tecnologias']) && count($filtros['tecnologias']) > 0) {
            $query->whereHas('tecnologias', function ($q) use ($filtros) {
                $q->whereIn('tecnologias.id_tecnologia', $filtros['tecnologias']);
            });
        }

        // Restringir a vacantes activamente públicas
        return $query->where('estado', 'PUBLICADA');
    }
}
