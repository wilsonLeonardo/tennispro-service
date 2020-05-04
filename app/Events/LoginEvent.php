<?php

namespace App\Events;

use App\Model\User;
use Illuminate\Queue\SerializesModels;

class LoginEvent
{
    use SerializesModels;

    private $user;
    private $origin;
    private $latitude;
    private $longitude;

    public function __construct(User $user, $origin, $coordinates)
    {
        $this->user = $user;
        $this->origin = $origin;
        $this->latitude = $coordinates && $coordinates['latitude'] ? $coordinates['latitude'] : null;
        $this->longitude = $coordinates && $coordinates['longitude'] ? $coordinates['longitude'] : null;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public static function of(User $user, $origin, $coordinates)
    {
        return new LoginEvent($user, $origin, $coordinates);
    }
}