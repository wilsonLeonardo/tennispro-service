<?php

namespace App\Http\Controllers;

use App\Events\LoginEvent;
use App\Model\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        // return response()->json(['request' => $request]);
        // return response()->json(['opa' => 'opaa']);


        $credentials = $request->only('email', 'password');
        $coordinates = $request->only('latitude', 'longitude');

        if ($token = auth()->attempt($credentials)) {
            event(LoginEvent::of(auth()->user(), 'SIMPLE_AUTHENTICATION', $coordinates));

            return $this->respondWithToken($token, auth()->user());
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function register(Request $request)
    {
        return $this->respondWithToken(
            auth()->login(UserService::create($request->all())),
            auth()->user()
        );
    }

    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'profile' => $user->profile,
            ],
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 * 24
        ]);
    }
}
