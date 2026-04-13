<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centro extends Model
{
    protected $primaryKey = 'id_centro';
    protected $fillable = ['nombre'];
}
