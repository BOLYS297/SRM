<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeRequete extends Model
{
    use HasFactory;

    protected $table = 'types_requetes';

    protected $fillable = [
        'libelle',
        'delai_cible_hrs',
    ];

    public function requetes()
    {
        return $this->hasMany(Requete::class);
    }
}
