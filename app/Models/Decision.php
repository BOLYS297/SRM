<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_decision',
        'resultat',
        'motif',
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

    public function notification()
    {
        return $this->hasOne(Notification::class);
    }
}
