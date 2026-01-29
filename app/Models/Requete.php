<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requete extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_depot',
        'objet',
        'description',
        'statut',
        'annee_depot',
        'filiere_depot',
        'niveau_depot',
        'etudiant_id',
        'type_requete_id',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function typeRequete()
    {
        return $this->belongsTo(TypeRequete::class);
    }

    public function etapeTraitements()
    {
        return $this->hasMany(EtapeTraitement::class)->orderBy('ordre_etape');
    }

    public function decision()
    {
        return $this->hasOne(Decision::class);
    }

    public function piecesJointes()
    {
        return $this->hasMany(PieceJointe::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
