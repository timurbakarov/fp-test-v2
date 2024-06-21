<?php

declare(strict_types=1);

namespace App\Spec;

use App\TemplateParserException;
use App\Spec;

class IntSpec extends Spec
{
    public const string SYMBOL = 'd';

    public static function isChar(string $char): bool
    {
        return $char === self::SYMBOL;
    }

    public function makeValue(mixed $parameter): int|string
    {
        if (is_int($parameter) || is_float($parameter)) {
            return (int)$parameter;
        }

        if (is_null($parameter)) {
            return 'NULL';
        }

        if (is_bool($parameter)) {
            return $parameter ? 1 : 0;
        }

        throw TemplateParserException::invalidSpecParam();
    }
}
