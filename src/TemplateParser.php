<?php

declare(strict_types=1);

namespace App;

readonly class TemplateParser
{
    public function __construct(
        private ConditionalsParser $conditionalsParser,
        private PlaceholdersParser $placeholdersParser,
    ) {
    }

    public function parse(string $query, array $args): string
    {
        $query = $this->conditionalsParser->parse($query, $args);

        return $this->placeholdersParser->parse($query, $args);
    }
}
