<?php

namespace App\Traits;

use App\Utils\ValidateBuilder;
use Illuminate\Validation\UnauthorizedException;

trait RestControllerTrait
{
    public function response($data)
    {
        return response()->json($data)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function paginate($query, $currentPage = 1, $perPage = 10, $transformer = null)
    {
        $data = $query->paginate($perPage, ["*"], "page", $currentPage);

        if ($transformer != null)
        {
            foreach ($data as $item)
            {
                $transformer($item);
            }
        }

        return $this->response($data);
    }

    public function checkProfile($profile) {
        if (auth()->user()->profile != $profile) throw new UnauthorizedException();
    }

    public function preconditions()
    {
        $vm = $this;

        return new ValidateBuilder(function($request, $rules, $messages) use ($vm) {
            $vm->validate($request, $rules, [], $messages);
        });
    }
}