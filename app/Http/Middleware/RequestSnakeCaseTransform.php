<?php

namespace App\Http\Middleware;

use Closure;

class RequestSnakeCaseTransform
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->replace($this->snakeKeys($request->all()));

        return $next($request);
    }

    private function snakeKeys($array, $delimiter = '_')
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->snakeKeys($value, $delimiter);
            }
            $result[snake_case($key, $delimiter)] = $value;
        }

        return $result;
    }
}
