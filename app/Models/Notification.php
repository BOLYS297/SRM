<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'etudiant_id',
        'requete_id',
        'decision_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function requete()
    {
        return $this->belongsTo(Requete::class);
    }

    public function decision()
    {
        return $this->belongsTo(Decision::class);
    }
}
