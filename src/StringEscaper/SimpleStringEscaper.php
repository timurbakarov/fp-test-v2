<?php

declare(strict_types=1);

namespace App\StringEscaper;

use App\StringEscaper;

class SimpleStringEscaper implements StringEscaper
{
    public function escape(string $string): string
    {
        return str_replace('\'', '\\\'', $string);
    }
}
