<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Traits\RestControllerTrait;
use App\Model\User;
use Illuminate\Http\Request;

class MobileUserController extends Controller
{
    use RestControllerTrait;

    public function store(Request $request)
    {

        return $this->respondWithToken(
            auth('api')->login(UserService::create($request->all())));
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
