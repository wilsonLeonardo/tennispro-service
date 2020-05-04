<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\User;
use App\Model\Campeonato;
use App\Traits\Uuids;

class CampeonatoInscrito extends Model
{
    use Uuids;
    
    protected $fillable = [
        'camp', 'user', 
    ];

    protected $keyType = 'uuid';

    public function camp()
    {
        return $this->belongsTo(Campeonato::class, 'camp_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
