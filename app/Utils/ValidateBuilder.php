<?php

namespace App\Utils;

class ValidateBuilder
{
    private $request;
    private $rules;
    private $messages;
    private $callback;

    public function __construct($callback)
    {
        $this->rules = [];
        $this->messages = [];
        $this->callback = $callback;
    }

    public function request($request)
    {
        $this->request = $request;

        return $this;
    }

    public function rules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    public function messages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    public function check()
    {
        call_user_func($this->callback, $this->request, $this->rules, $this->messages);
    }
}