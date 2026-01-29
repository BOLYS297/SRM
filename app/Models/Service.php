<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_service',
        'type_service',
    ];

    public function etapeTraitements()
    {
        return $this->hasMany(EtapeTraitement::class);
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    public function isCourrier(): bool
    {
        if ($this->type_service && strcasecmp($this->type_service, 'Courrier') === 0) {
            return true;
        }

        return stripos($this->nom_service, 'courrier') !== false;
    }
}
