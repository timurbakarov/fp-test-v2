<?php

declare(strict_types=1);

namespace App\Spec;

use App\Spec;

class NullSpec extends Spec
{
    private const string CHAR_SPACE = ' ';
    private const string CHAR_EMPTY = '';

    public static function isChar(string $char): bool
    {
        return $char === self::CHAR_SPACE || $char === self::CHAR_EMPTY;
    }

    public function makeValue(mixed $parameter): string|int|float
    {
        return $this->formatParameter($parameter);
    }
}
