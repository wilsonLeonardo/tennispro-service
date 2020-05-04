<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use App\Model\Campeonato;
use App\Model\User;
use App\Model\CampeonatoInscrito;
use Illuminate\Support\Facades\Input;

class UserController extends Controller
{
    public function updateDeviceIdentifier(Request $request)
    {
        $roles = [ 'device_identifier' => 'required' ];
        $labels = [ 'device_identifier' => 'ID do Aparelho' ];

        
        
        $request->validate($roles, [], $labels);

        $user = User::findOrFail(auth()->user()->getAuthIdentifier());

        $user->setDeviceIdentifier($request->device_identifier);

        $user->save();
    }
    public function findTeacher()
    {
       $teacher =  User::query()
        ->with('clubs')
        ->where('profile', '=', 'TEACHER')
        ->get();

        // $teacher = $teacher->toArray();
        // //dd($teacher);
        return collect($teacher->toArray())->map(function($teacher) {
            return [
                "clubs" => $teacher['clubs'],
                "email"=>$teacher['email'],
                "id" => $teacher['id'],
                "image"=> $teacher['image'] ? url('storage/users/'.$teacher['image']) : null,
                "name"=>$teacher['name'],
                "nascimento"=>$teacher['nascimento'],
                "nivel"=>$teacher['nivel'],
                "preço"=>$teacher['preço'],
                "telefone"=>$teacher['telefone'],
                "username"=>$teacher['username']
            ];
         });
    }
    public function findCamps()
    {
        $camp = Campeonato::query()->where('status','=', 'PROGRESS')->get();

        return response()->json($camp);

    }
    public function findUsers()
    {
        $users = User::query()->where('profile','=', 'USER')->where('id', '!=', auth()->user()->id)->get();

        return collect($users->toArray())->map(function($user) {
            return [
                'id' => $user['id'],
                'name' => $user['name']
            ];
        })->toArray();

    }
    public function findMyCamps()
    {
        $camp = CampeonatoInscrito::query()->with('camp')
            ->where('user_id', auth()->user()->getAuthIdentifier())
            ->whereHas('camp', function ($query) {
                $query->where('status', '=', 'PROGRESS');
            })
            ->get();

        return response()->json($camp);

    }
    public function addPicture(Request $request)
    {
        $user = auth()->user();

        if($request->hasFile('picture') && $request->file('picture')->isValid()){
            if($user->image)
                $name = $user->image;
            else
                $name = $user->id.kebab_case($user->name);
                
                $extension = $request->picture->extension();
                $nameFile = "{$name}.{$extension}";
            


            $data['image'] = $nameFile;
            $upload = $request->file('picture')->storeAs('users', $nameFile);

            $user->update($data);
            Log::alert($user);
            

            if(!$upload)
                return response()->json(['error' => 'Imagem com erro!'], 500);
        }
    }
}
