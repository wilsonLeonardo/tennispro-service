<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class ClearNotificationEvent
{
    use SerializesModels;

    private $userId;
    private $type;

    public function __construct($userId, $type)
    {
        $this->userId = $userId;
        $this->type = $type;
    }

    public static function of($userId, $type)
    {
        return new ClearNotificationEvent($userId, $type);
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getType()
    {
        return $this->type;
    }
}