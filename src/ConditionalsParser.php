<?php

declare(strict_types=1);

namespace App;

class ConditionalsParser
{
    const string CONDITIONAL_OPEN_SYMBOL = '{';
    const string CONDITIONAL_CLOSE_SYMBOL = '}';

    public function parse(string $query, array $args): string
    {
        $openBracketExists = str_contains($query, self::CONDITIONAL_OPEN_SYMBOL);
        $closeBracketExists = str_contains($query, self::CONDITIONAL_CLOSE_SYMBOL);

        if (!$openBracketExists && !$closeBracketExists) {
            return $query;
        }

        $openBracketPosition = null;
        $closeBracketPosition = null;

        $placeholderPositions = [];
        $charIndex = 0;
        $argsIndex = 0;

        while($char = $this->nextChar($query, $charIndex)) {
            if ($char === self::CONDITIONAL_OPEN_SYMBOL) {
                if ($openBracketPosition !== null) {
                    throw new TemplateParserException('nested conditionals are not supported');
                }

                $openBracketPosition = $charIndex;
                $charIndex++;
                continue;
            }

            if ($char === Placeholder::SYMBOL) {
                var_dump($argsIndex, $charIndex, $char, $query);

                if ($openBracketPosition !== null) {
                    if (!array_key_exists($argsIndex, $args)) {
                        throw new TemplateParserException('parameter data in conditional is missing');
                    }

                    $placeholderPositions[] = $argsIndex;
                }

                $argsIndex++;
                $charIndex++;
                continue;
            }

            if ($char === self::CONDITIONAL_CLOSE_SYMBOL) {
                if ($openBracketPosition === null) {
                    throw new TemplateParserException('open bracket is missing');
                }

                if (count($placeholderPositions) === 0) {
                    throw new TemplateParserException('parameter in conditional is missing');
                }

                $closeBracketPosition = $charIndex;

                if ($this->hasSkips($placeholderPositions, $args)) {
                    $query = $this->removeConditional($query, $openBracketPosition, $closeBracketPosition);
                    $charIndex = $openBracketPosition - 2;
                } else {
                    $query = $this->removeBrackets($query, $openBracketPosition, $closeBracketPosition);
                    $charIndex = $closeBracketPosition - 2;
                }

                $openBracketPosition = null;
                $closeBracketPosition = null;

                $charIndex++;
                continue;
            }

            $charIndex++;
        }

        if ($openBracketPosition !== null && $closeBracketPosition === null) {
            throw new TemplateParserException('closing bracket is missing');
        }

        return $query;
    }

    private function hasSkips(array $placeholderPositions, array $parameters): bool
    {
        $skips = array_filter($placeholderPositions, function (int $position) use ($parameters) {
            return $parameters[$position] === Skip::class;
        });

        return count($skips) > 0;
    }

    private function removeConditional(string $query, int $openBracketPosition, int $closeBracketPosition): string
    {
        return Str::substrReplace(
            input: $query,
            start: $openBracketPosition,
            length: $closeBracketPosition - $openBracketPosition + 1,
            replace: '',
        );
    }

    private function removeBrackets(string $query, int $openBracketPosition, int $closeBracketPosition): string
    {
        $query = Str::substrReplace(
            input: $query,
            start: $openBracketPosition,
            length: 1,
            replace: '',
        );

        return Str::substrReplace(
            input: $query,
            start: $closeBracketPosition - 1,
            length: 1,
            replace: '',
        );
    }

    private function nextChar(string $value, int $index): string
    {
        return mb_substr($value, $index, 1);
    }
}
