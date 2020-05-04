<?php

namespace App\Utils;

class CollectionUtils
{
    public static function mapToIds($arr)
    {
        return array_map(function($item) {
            return $item['id'];
        }, $arr);
    }
}