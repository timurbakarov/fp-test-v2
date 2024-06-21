<?php

declare(strict_types=1);

namespace App;

readonly class Database implements DatabaseInterface
{
    public function __construct(
        private \mysqli $mysqli,
        private TemplateParser $templateParser,
    ) {
    }

    public function buildQuery(string $query, array $args = []): string
    {
        return $this->templateParser->parse($query, $args);
    }

    public function skip(): string
    {
        return Skip::class;
    }
}
