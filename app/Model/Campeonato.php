<?php

namespace App\Model;
use App\Traits\Uuids;
use App\Model\CampeonatoInscrito;
use App\Model\User;

use Illuminate\Database\Eloquent\Model;

class Campeonato extends Model
{
    use Uuids;

    protected $keyType = 'uuid';

    protected $fillable = [
        'name', 'endereco', 'niveis','valor_premio', 'taxa_inscricao', 'status'
    ];
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function inscritos()
    {
        return $this->hasMany(CampeonatoInscrito::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}