<?php

declare(strict_types=1);

namespace App;

/**
 * @method static self invalidSpecParam()
 */
class TemplateParserException extends \Exception
{
    public const int INVALID_SPEC_PARAM = 1;

    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function make(int $code): self
    {
        return new self(self::errorMessage($code), $code);
    }

    private static function errorMessage(int $code): string
    {
        return match ($code) {
            self::INVALID_SPEC_PARAM => 'invalid spec param',
            default => throw new \Exception('invalid code'),
        };
    }

    public static function __callStatic(string $name, array $arguments): self
    {
        return match ($name) {
            'invalidSpecParam' => self::make(self::INVALID_SPEC_PARAM),
            default => throw new \Exception('invalid name'),
        };
    }
}
