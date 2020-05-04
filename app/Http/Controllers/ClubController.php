<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Club;
use App\Services\ClubService;
use App\Traits\RestControllerTrait;
use Illuminate\Support\Facades\DB;

class ClubController extends Controller
{
    use RestControllerTrait;

    public function store(Request $request)
    {
        $clubId = ClubService::createClub($request->all());
        
        return $this->respondWithToken(
            auth('api')->login(ClubService::createUser($request->all(), $clubId)));
    }
    public function find()
    {
        $clubs = DB::table('clubs')->select('id', 'name')->get();

        return $clubs;
    }

    public function update(Request $request)
    {
   
        $data = $request->all();
        $data['id'] = auth()->user()->getAuthIdentifier();
 
        $clubId = ClubService::updateUser($data);
        $data['id'] = $clubId;

        return $this->response(ClubService::update($data));
    }

    public function index()
    {
        $user = User::query()->with('clubs.club')->findOrFail(auth()->user()->getAuthIdentifier());
        $club = $user->toArray()['clubs'][0]['club'];

        return [
            "name" => $user['name'],
            "email" => $user['email'],
            "telefone"=> $club['telefone'],
            "cep"=> "".$club['cep']."",
            "quadras"=> "".$club['quadras']."",
            "aluguel_price"=> $club['aluguel_price'],
            "mensal_price"=> $club['mensal_price'],
        ];
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => auth('api')->user()->id,
                'username' => auth('api')->user()->username,
                'profile' => auth('api')->user()->profile,
            ],
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 * 24
        ]);
    }
}
