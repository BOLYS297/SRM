<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PieceJointe extends Model
{
    use HasFactory;

    protected $table = 'pieces_jointes';

    protected $fillable = [
        'nom_fichier',
        'type_piece',
        'chemin_fichier',
        'date_ajout',
        'requete_id',
    ];

    public function requete()
    {
        return $this->belongsTo(Requete::class);
    }
}
