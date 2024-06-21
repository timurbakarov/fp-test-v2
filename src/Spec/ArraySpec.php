<?php

declare(strict_types=1);

namespace App\Spec;

use App\TemplateParserException;
use App\Spec;

class ArraySpec extends Spec
{
    private const string SYMBOL = 'a';

    public static function isChar(string $char): bool
    {
        return $char === self::SYMBOL;
    }

    public function makeValue(mixed $parameter): string
    {
        if (!is_array($parameter)) {
            throw new TemplateParserException("invalid parameter");
        }

        // list
        if (array_key_exists(0, $parameter)) {
            return implode(
                ', ',
                array_map(fn(mixed $val) => $this->formatParameter($val), $parameter),
            );
        }

        // associative array
        $value = [];
        foreach ($parameter as $name => $val) {
            $value[] = '`' . $name . '` = ' . $this->formatParameter($val);
        }

        return implode(', ', $value);
    }
}
