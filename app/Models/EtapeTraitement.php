<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeTraitement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordre_etape',
        'action',
        'date_entree',
        'date_sortie',
        'observation',
        'requete_id',
        'service_id',
    ];

    public function requete()
    {
        return $this->belongsTo(Requete::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
