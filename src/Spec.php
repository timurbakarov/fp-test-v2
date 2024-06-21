<?php

declare(strict_types=1);

namespace App;

use App\Spec\ArraySpec;
use App\Spec\FloatSpec;
use App\Spec\IdentifierSpec;
use App\Spec\IntSpec;
use App\Spec\NullSpec;

abstract class Spec
{
    private const array SPECS = [
        NullSpec::class,
        IdentifierSpec::class,
        IntSpec::class,
        FloatSpec::class,
        ArraySpec::class,
    ];

    abstract public static function isChar(string $char): bool;
    abstract public function makeValue(mixed $parameter): mixed;

    public function __construct(protected readonly StringEscaper $stringEscaper)
    {
    }

    public static function make(string $char, StringEscaper $stringEscaper): static
    {
        /** @var self $specClass */
        foreach (self::SPECS as $specClass) {
            if ($specClass::isChar($char)) {
                return new $specClass($stringEscaper);
            }
        }

        throw new TemplateParserException("invalid spec " . $char);
    }

    final public function length(): int
    {
        return $this instanceof NullSpec ? 1 : 2;
    }

    protected function formatParameter(mixed $parameter): string|int|float
    {
        if (is_string($parameter)) {
            return "'" . $this->stringEscaper->escape($parameter) . "'";
        }

        if (is_int($parameter) || is_float($parameter)) {
            return $parameter;
        }

        if (is_bool($parameter)) {
            return $parameter ? 1 : 0;
        }

        if (is_null($parameter)) {
            return 'NULL';
        }

        throw TemplateParserException::invalidSpecParam();
    }
}
