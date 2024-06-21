<?php

declare(strict_types=1);

namespace App;

interface StringEscaper
{
    public function escape(string $string): string;
}
