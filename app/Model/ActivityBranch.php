<?php

namespace App\Model;

class ActivityBranch extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'order',
    ];

    public static function roles() {
        return [
            'name' => 'required|max:200',
            'description' => 'required|max:500',
        ];
    }

    public static function mappedProperties()
    {
        return [
            'name' => 'Nome',
            'description' => 'Descrição',
        ];
    }
}
