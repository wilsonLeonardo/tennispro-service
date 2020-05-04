<?php

namespace App\Services;

use App\Model\Message;
use App\Model\User;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public static function insert($ownerId, $foreignId){
        return DB::transaction(function () use ($ownerId, $foreignId) {
            $new = new Message();

            $new->owner()->associate(User::findOrFail($ownerId));
            $new->foreign()->associate(User::findOrFail($foreignId));
            
            $new->save();

            return $new;
        });
    }
    public static function findOneByForeignAndOwner($ownerId, $foreignId)
    {
        $new = Message::
            with('foreign')
            ->with('owner')
            ->where('owner_id', '=', $ownerId)
            ->where('foreign_id', '=', $foreignId)
            ->first();

        return $new;
    }
    
    public static function findAllByUser($user)
    {
        $messages = collect(MessageService::receiptMessages($user->id));

        foreach (MessageService::sentMessages($user->id) as $message)
        {
            $messages->push($message);
        }

        return $messages->toArray();
    }
    public static function receiptMessages($userId)
    {
        $messages = Message::with('foreign')
            ->whereHas('owner', function($query) use ($userId) {
                $query->where('id', $userId);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return collect($messages->toArray())->map(function($message) {
            return [
                'id' => $message['id'],
                'avatar' => $message['foreign']['image'] ? url('storage/users/'.$message['foreign']['image']) : null,
                'name' => $message['foreign']['name'],
                'lastMessage' => $message['last_message'],
                'updatedAt' => $message['updated_at'],
                'pending' => $message['pending_by'] == 'FOREIGN',
            ];
        })->toArray();
    }

    public static function sentMessages($userId)
    {
        $messages = Message::with('owner')
            ->whereHas('foreign', function($query) use ($userId) {
                $query->where('id', $userId);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return collect($messages->toArray())->map(function($message) {
            return [
                'id' => $message['id'],
                'avatar' => $message['owner']['image'] ? url('storage/users/'.$message['owner']['image']): null,
                'name' => $message['owner']['name'],
                'lastMessage' => $message['last_message'],
                'updatedAt' => $message['updated_at'],
                'pending' => $message['pending_by'] == 'OWNER',
            ];
        })->toArray();
    }

    public static function getChat($messageId)
    {
        return FirebaseService::findByKey('messages/' . $messageId);
    }
}