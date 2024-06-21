<?php

declare(strict_types=1);

namespace App\StringEscaper;

use App\StringEscaper;

class MysqliEscaper implements StringEscaper
{
    public function __construct(private readonly \mysqli $mysql)
    {
    }

    public function escape(string $string): string
    {
        return $this->mysql->real_escape_string($string);
    }
}
