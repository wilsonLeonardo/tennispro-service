<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Validation\UnauthorizedException;

class CheckProfile
{
    public function handle($request, Closure $next, $role)
    {
        $authenticatedUser = $request->user();

        if (isset($authenticatedUser) && $authenticatedUser->profile == $role)
        {
            return $next($request);
        }

        throw new UnauthorizedException();
    }
}