<?php

namespace App\Model;
use App\Model\User;
use App\Traits\Uuids;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use Uuids;

    protected $keyType = 'uuid';
    
    protected $fillable = [
        'foreign_id', 'owner_id', 'last_message', 'approved', 'pending_by'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function foreign()
    {
        return $this->belongsTo(User::class, 'foreign_id');
    }

    public function approve()
    {
        $this->approved = true;
    }

    public function isPendingByOwner()
    {
        return $this->pending_by == 'OWNER';
    }

    public function isPendingByForeign()
    {
        return $this->pending_by == 'FOREIGN';
    }

    public function setPendingByOwner()
    {
        $this->pending_by = 'OWNER';
    }

    public function setPendingByForeign()
    {
        $this->pending_by = 'FOREIGN';
    }

    public function setNotPending()
    {
        $this->pending_by = '';
    }
}
