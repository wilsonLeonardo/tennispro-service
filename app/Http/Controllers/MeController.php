<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use App\Events\ClearNotificationEvent;
use App\Services\UserService;
use App\Traits\RestControllerTrait;
use App\Model\User;
use Illuminate\Http\Request;
use App\Model\Club;
use App\Model\ClubUser;

class MeController extends Controller
{
    use RestControllerTrait;

    public function index()
    {
        $user = User::findOrFail(auth()->user()->getAuthIdentifier());

        $user = $user->toArray();

        if(isset($user['image'])){
            return [
                'Username' => $user['username'],
                'Email' => $user['email'],
                'avatarUri' =>  url('storage/users/'.$user['image'])
            ];
        }else{
            return [
                'Username' => $user['username'],
                'Email' => $user['email']
            ];
        }

    }

    public function indexTeacher()
    {
        $user = User::findOrFail(auth()->user()->getAuthIdentifier());

        $user = $user->toArray();

        if(isset($user['image'])){
            return [
                "name" => $user['name'],
                "email" => $user['email'],
                "nascimento" => $user['nascimento'],
                "username" => $user['username'],
                "telefone"=> $user['telefone'],
                "preço"=> $user['preço'],
                'avatarUri' =>  url('storage/users/'.$user['image'])
            ];
        }else {
            return [
                "name" => $user['name'],
                "email" => $user['email'],
                "nascimento" => $user['nascimento'],
                "username" => $user['username'],
                "telefone"=> $user['telefone'],
                "preço"=> $user['preço'],
            ];
        }
    }
    public function indexAccount()
    {
        $user = User::with('clubs.club')->findOrFail(auth()->user()->getAuthIdentifier());

        $user = $user->toArray();


        return [
            "name" => $user['name'],
            "email" => $user['email'],
            "nascimento" => $user['nascimento'],
            "username" => $user['username'],
            "telefone"=> $user['telefone'],
            "clubs" => $user['clubs']
        ];
    }
    public function indexMyClub()
    {
        $myClubs = ClubUser::with('club')->where('user_id', auth()->user()->getAuthIdentifier())->get();

        return response()->json($myClubs);
    }

    public function update(Request $request)
    {
        $this->preconditions()
            ->request($request)
            ->rules(User::roles())
            ->messages(User::mappedProperties())
            ->check();
        
        $data = $request->all();
        $data['id'] = auth()->user()->getAuthIdentifier();
 

        return $this->response(UserService::update($data));
    }
    public function addClub($id){
        $club = new ClubUser();
        $clubExist = ClubUser::where('club_id', $id)->where('user_id', auth()->user()->getAuthIdentifier())->first();

        if(!isset($clubExist)){
            $club->club()->associate(Club::findOrfail($id));
            $club->user()->associate(User::findOrfail(auth()->user()->getAuthIdentifier()));
            $club->save();

            return response()->json($club);
        }else{
            return response()->json(['error' => 'Você já esta cadastrado nesse clube!'], 500);     

        }
        
    }
    public function messages(Request $request)
    {
        event(ClearNotificationEvent::of(auth()->user()->getAuthIdentifier(), 'messages'));

        return $this->response(MessageService::findAllByUser(auth()->user()));
    }
}
