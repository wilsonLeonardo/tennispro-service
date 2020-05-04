<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Club;
use App\Model\User;
use App\Traits\Uuids;

class ClubUser extends Model
{
    use Uuids;
    
    protected $fillable = [
        'club', 'user', 
    ];

    protected $keyType = 'uuid';

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
