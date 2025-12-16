<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Premio extends Model
{
    protected $fillable = ['nombre', 'entregado'];

    public function participante()
    {
        return $this->hasOne(Participante::class);
    }
}
