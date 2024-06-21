<?php

declare(strict_types=1);

namespace App;

class Str
{
    public static function substrReplace(string $input,  int $start, int $length, string $replace): string
    {
        return mb_substr($input, 0, $start) . $replace . mb_substr($input, $start + $length);
    }
}
