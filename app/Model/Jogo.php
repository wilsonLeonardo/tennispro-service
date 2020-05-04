<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Jogo extends Model
{
    use Uuids;

    protected $fillable = [
        'winner', 'status', 'hora', 'dia'
    ];    
    protected $keyType = 'uuid';

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function foreign()
    {
        return $this->belongsTo(User::class, 'foreign_id');
    }
    public function setOwnerWin()
    {
        $this->winner = 'OWNER';
    }
    public function setForeignWin()
    {
        $this->winner = 'FOREIGN';
    }
}
