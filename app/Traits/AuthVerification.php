<?php

namespace App\Traits;

trait AuthVerification
{
    public function authorize($root, $args)
    {
        return auth()->user() !== null;
    }
}