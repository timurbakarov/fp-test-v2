<?php

declare(strict_types=1);

namespace App\Spec;

use App\TemplateParserException;
use App\Spec;

class FloatSpec extends Spec
{
    private const string SYMBOL = 'f';

    public static function isChar(string $char): bool
    {
        return $char === self::SYMBOL;
    }

    public function makeValue(mixed $parameter): int|float|string
    {
        if (is_int($parameter) || is_float($parameter)) {
            return $parameter;
        }

        if (is_null($parameter)) {
            return 'NULL';
        }

        throw TemplateParserException::invalidSpecParam();
    }
}
