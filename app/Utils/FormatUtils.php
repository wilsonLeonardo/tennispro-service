<?php

namespace App\Utils;

class FormatUtils
{
    public static function money($value, $type)
    {
        if (isset($type) && $type == 'DOLAR')
        {
            $money = number_format($value, 2);
            $money = str_replace('.', '-', $money);
            $money = str_replace(',', ',', $money);
            $money = str_replace('-', ',', $money);

            return '$ ' . $money;
        }

        $money = number_format($value, 2);
        $money = str_replace(',', '-', $money);
        $money = str_replace('.', ',', $money);
        $money = str_replace('-', '.', $money);

        return 'R$ ' . $money;
    }

    public static function thousandsSeparator($value)
    {
        return number_format($value, 0, ',', '.');
    }
}