<?php

namespace App\Events;

use App\Model\Message;
use Illuminate\Queue\SerializesModels;

class SendMessageEvent
{
    use SerializesModels;

    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getReceiver()
    {
        return $this->message->owner->id == auth()->user()->getAuthIdentifier()
            ? $this->message->owner
            : $this->message->foreign;
    }

    public static function of(Message $message)
    {
        return new SendMessageEvent($message);
    }
}