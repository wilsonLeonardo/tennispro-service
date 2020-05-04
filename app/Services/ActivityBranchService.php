<?php

namespace App\Services;

use App\Model\ActivityBranch;

class ActivityBranchService
{
    public static function save($data) {
        $field = isset($data['id']) ? ActivityBranch::findOrFail($data['id']) : new ActivityBranch();
        $field->fill($data);

        return $field->save();
    }

}