<?php

namespace App\Model;
use App\Traits\Uuids;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use Uuids;

    protected $keyType = 'uuid';

    protected $fillable = [
        'cep', 'quadras', 'aluguel_price','mensal_price', 'name','telefone'
    ];
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
