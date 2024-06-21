<?php

declare(strict_types=1);

namespace App\Spec;

use App\TemplateParserException;
use App\Spec;

class IdentifierSpec extends Spec
{
    private const string SYMBOL = '#';

    public static function isChar(string $char): bool
    {
        return $char === self::SYMBOL;
    }

    public function makeValue(mixed $parameter): string
    {
        if (is_array($parameter)) {
            $params = array_map(
                fn(string $value) => $this->escapeIdentifier($value),
                $parameter,
            );

            return implode(', ', $params);
        }

        if (is_string($parameter)) {
            return $this->escapeIdentifier($parameter);
        }

        throw new TemplateParserException("invalid value");
    }

    private function escapeIdentifier(string $value): string
    {
        return '`' . $this->stringEscaper->escape($value) . '`';
    }
}
