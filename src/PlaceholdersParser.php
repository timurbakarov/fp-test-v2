<?php

declare(strict_types=1);

namespace App;

readonly class PlaceholdersParser
{
    public function __construct(private StringEscaper $stringEscaper)
    {
    }

    public function parse(string $query, array $args): string
    {
        $argsIndex = 0;
        while ($placeholder = $this->findPlaceholder($query)) {
            $query = $placeholder->replace($query, $args[$argsIndex]);
            $argsIndex++;
        }

        return $query;
    }

    private function findPlaceholder(string $query): Placeholder|false
    {
        $index = 0;
        while ($char = $this->getChar($query, $index)) {
            if ($char !== Placeholder::SYMBOL) {
                $index++;
                continue;
            }

            $spec = $this->getSpecSymbol($query, $index);

            return new Placeholder($index, Spec::make($spec, $this->stringEscaper));
        }

        return false;
    }

    private function getSpecSymbol(string $query, int $index): string
    {
        return mb_substr($query, $index + 1, 1);
    }

    private function getChar($query, int $index): string
    {
        return mb_substr($query, $index, 1);
    }
}
