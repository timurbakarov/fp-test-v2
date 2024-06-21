<?php

declare(strict_types=1);

namespace App;

readonly class Placeholder
{
    public const string SYMBOL = '?';

    public function __construct(public int $position, public Spec $spec)
    {
    }

    public function replace(string $query, mixed $parameter): string
    {
        return $this->substrReplace(
            input: $query,
            start: $this->position,
            length: $this->spec->length(),
            replace: $this->spec->makeValue($parameter),
        );
    }

    private function substrReplace(string $input,  int $start, int $length, mixed $replace): string
    {
        return mb_substr($input, 0, $start) . $replace . mb_substr($input, $start + $length);
    }
}
