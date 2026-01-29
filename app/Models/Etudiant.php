<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'date_naissance',
        'telephone',
        'email',
    ];

    public function requetes()
    {
        return $this->hasMany(Requete::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
