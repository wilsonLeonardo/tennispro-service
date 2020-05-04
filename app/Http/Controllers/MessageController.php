<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Http\Request;
use App\Traits\RestControllerTrait;
use App\Services\FirebaseService;
use App\Services\MessageService;
use App\Model\Message;
use Illuminate\Support\Facades\Log;
use App\Events\SendMessageEvent;

class MessageController extends Controller
{
    use RestControllerTrait;

    public function store(Request $request){
        $foreignId = $request->all()['foreign'];

        $exists = Message::where(function ($query) use($foreignId){
            $query->where('owner_id','=', auth()->user()->id)
                ->where('foreign_id', '=', $foreignId);
        })->orWhere(function ($query) use($foreignId){
            $query->where('owner_id','=', $foreignId)
                ->where('foreign_id', '=', auth()->user()->id);
        })->first();

        if($exists)
            return response()->json(['error' => 'Você já possue esse contato, vá para o chat!'], 500);     

        //Find funcionando
        $new = MessageService::findOneByForeignAndOwner($foreignId, auth()->user()->id);
        if(!isset($new))
        {
            //Inserir funcionando
            $new = MessageService::insert($foreignId, auth()->user()->id);        
            
        }
        $new->approve();
        $new->save();

        if($new['foreign']['id'] == auth()->user()->id){
            return [
                'id' => $new['id'],
                'name' => $new['owner']['name'],
                'avatar'=> $new['owner']['image']
            ];
        }else{
            return [
                'id' => $new['id'],
                'name' => $new['foreign']['name'],
                'avatar'=> $new['foreign']['image']
            ];
        }

    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [ 'content' => 'required' ], [], [ 'content' => 'A mensagem é um campo obrigatório.' ]);
        $data = $request->toArray();
        
        
        $message = DB::transaction(function() use ($data, $id) {
            $message = Message::with('owner')
            ->with('foreign')
            ->findOrFail($id);
            
            
            $message->fill(['last_message' => $data['content']]);
            
            $isAuthor = $message->owner->id == auth()->user()->getAuthIdentifier();

            if ($isAuthor)
            {
                $message->setPendingByOwner();
            }
            else
            {
                $message->setPendingByForeign();
            }

            $message->save();

            FirebaseService::push(
                'messages/' . $message->id,
                [
                    'userId' => auth()->user()->getAuthIdentifier(),
                    'content' => $data['content'],
                    'time' => $message->updated_at->format('Y-m-d H:i:s'),
                ]
            );

            return $message;
        });

        event(SendMessageEvent::of($message));
    }

    public function getMessageView($id)
    {
        $loggedUserId = auth()->user()->getAuthIdentifier();
        $message = Message::with('foreign')
        ->with('owner')
        ->findOrFail($id);
        
        $messagesList = MessageService::getChat($id);
        
        $foreignId = $message->foreign->id; 
        $messageOwnerId = $message->owner->id;

        if (!in_array($loggedUserId, [ $foreignId, $messageOwnerId ])) {
            throw new ModelNotFoundException();
        }


        $isForeign = $message->foreign->id == $loggedUserId;
        $isReceiptMessage = $isForeign;


        if ($isReceiptMessage)
        {
            return [
                'id' => $message->foreign->id,
                'name' => $message->owner->name,
                'messages' => $messagesList
            ];
        }
        else
        {
            return [
                'id' => $message->foreign->id,
                'name' => $message->foreign->name,
                'messages' => $messagesList
            ];
        }
    }

    public function updateStatus($id)
    {
        $message = Message::with('owner')
            ->with('foreign')
            ->findOrFail($id);

        $isOwner = $message->owner->id == auth()->user()->getAuthIdentifier();
        $isForeign = $message->foreign->id == auth()->user()->getAuthIdentifier();

        
        
        
        if ($isForeign && $message->pending_by == 'OWNER')
        {
            $message->setNotPending();
            $message->save();
        }
        
        if ($isOwner && $message->pending_by == 'FOREIGN')
        {
            $message->setNotPending();
            $message->save();
        }        
    }
}
