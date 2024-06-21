<?php

declare(strict_types=1);

namespace Tests;

use App\ConditionalsParser;
use App\TemplateParserException;
use App\PlaceholdersParser;
use App\Skip;
use App\StringEscaper;
use App\TemplateParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TemplateParserTest extends TestCase
{
    private TemplateParser $templateParser;

    public function setUp(): void
    {
        parent::setUp();

        $stringEscaper = new StringEscaper\SimpleStringEscaper();
        $conditionalParser = new ConditionalsParser();
        $placeholdersParser = new PlaceholdersParser($stringEscaper);
        $this->templateParser = new TemplateParser($conditionalParser, $placeholdersParser);
    }

    #[DataProvider('successCases')]
    public function testSuccessCases(string $query, array $queryData, string $expectedResult): void
    {
        $parsedQuery = $this->templateParser->parse($query, $queryData);

        $this->assertSame($expectedResult, $parsedQuery);
    }

    public static function successCases(): array
    {
        return [
            // auto spec ?
            'it uses string with auto spec' => [
                'query' => 'SELECT ?',
                'expectedResult' => 'SELECT \'name\'',
                'queryData' => ['name'],
            ],
            'it escapes string with auto spec' => [
                'query' => 'SELECT ?',
                'expectedResult' => 'SELECT \'name\'',
                'queryData' => ['name'],
            ],
            'it uses int with auto spec' => [
                'query' => 'SELECT ?',
                'expectedResult' => 'SELECT 2',
                'queryData' => [2],
            ],
            'it uses float with auto spec' => [
                'query' => 'SELECT ?',
                'expectedResult' => 'SELECT 2.5',
                'queryData' => [2.5],
            ],
            'it uses bool with auto spec' => [
                'query' => 'SELECT ? , ?',
                'expectedResult' => 'SELECT 1 , 0',
                'queryData' => [true, false],
            ],
            'it uses null with auto spec' => [
                'query' => 'SELECT ?',
                'expectedResult' => 'SELECT NULL',
                'queryData' => [null],
            ],

            // int spec ?d
            'it uses int with int spec' => [
                'query' => 'SELECT ?d',
                'expectedResult' => 'SELECT 2',
                'queryData' => [2],
            ],
            'it converts float to int with int spec' => [
                'query' => 'SELECT ?d',
                'expectedResult' => 'SELECT 2',
                'queryData' => [2.5],
            ],
            'it converts null to NULL with int spec' => [
                'query' => 'SELECT ?d',
                'expectedResult' => 'SELECT NULL',
                'queryData' => [null],
            ],

            // float spec ?f
            'it uses float with float spec' => [
                'query' => 'SELECT name FROM users WHERE value = ?f',
                'expectedResult' => 'SELECT name FROM users WHERE value = 2.5',
                'queryData' => [2.5],
            ],
            'it uses null with float spec' => [
                'query' => 'SELECT name FROM users WHERE value = ?f',
                'expectedResult' => 'SELECT name FROM users WHERE value = NULL',
                'queryData' => [null],
            ],

            // identifier spec ?#
            'it uses string with identifier spec' => [
                'query' => 'SELECT ?#',
                'expectedResult' => 'SELECT `name`',
                'queryData' => ['name'],
            ],
            'it uses array with identifier spec' => [
                'query' => 'SELECT ?#',
                'expectedResult' => 'SELECT `name`, `email`',
                'queryData' => [['name', 'email']],
            ],
            'it escapes identifiers with identifier spec' => [
                'query' => 'SELECT ?# ?#',
                'expectedResult' => 'SELECT `na\\\'me` `em\\\'ail`',
                'queryData' => [['na\'me'], 'em\'ail'],
            ],

            // array spec ?a
            'it uses list with array spec' => [
                'query' => 'SELECT (?a)',
                'expectedResult' => 'SELECT (1, 2.5, \'na\\\'me\', 1, 0, NULL)',
                'queryData' => [[1, 2.5, 'na\'me', true, false, null]],
            ],
            'placeholder with array spec key value' => [
                'query' => 'UPDATE users SET ?a',
                'expectedResult' => 'UPDATE users SET `int` = 1, `float` = 2.5, `true` = 1, `false` = 0, `null` = NULL',
                'queryData' => [['int' => 1, 'float' => 2.5, 'true' => 1, 'false' => 0, 'null' => null]],
            ],

            // conditionals
            'it uses conditional' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?}',
                'queryData' => [1],
                'expectedResult' => 'SELECT name FROM users WHERE id = 1 AND block = 1',
            ],
            'it uses many conditionals' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?}{ AND block2 = ?d}',
                'expectedResult' => 'SELECT name FROM users WHERE id = 1 AND block = 1 AND block2 = 1',
                'queryData' => [true, true],
            ],
            'it skips conditional' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d}',
                'queryData' => [Skip::class],
                'expectedResult' => 'SELECT name FROM users WHERE id = 1',
            ],
            'it skips many conditionals' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d}{ AND block2 = ?d}',
                'queryData' => [Skip::class, Skip::class],
                'expectedResult' => 'SELECT name FROM users WHERE id = 1',
            ],
            'it skips one of conditionals' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d}{ AND block2 = ?d}',
                'queryData' => [Skip::class, 1],
                'expectedResult' => 'SELECT name FROM users WHERE id = 1',
            ],
            'it uses conditional with many placeholders' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d AND block2 = ?d}',
                'queryData' => [1, 2],
                'expectedResult' => 'SELECT name FROM users WHERE id = 1 AND block = 1 AND block2 = 2',
            ],
            'conditional with many placeholders and of them skip' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d AND block2 = ?d}',
                'queryData' => [Skip::class, 2],
                'expectedResult' => 'SELECT name FROM users WHERE id = 1',
            ],
            'conditional placeholder with many placeholders and of them skip 2' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d AND block2 = ?d}',
                'queryData' => [2, Skip::class],
                'expectedResult' => 'SELECT name FROM users WHERE id = 1',
            ],
        ];
    }

    #[DataProvider('failureCases')]
    public function testFailureCases(string $query, array $queryData, TemplateParserException $expectedException): void
    {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionCode($expectedException->getCode());

        $this->templateParser->parse($query, $queryData);
    }

    public static function failureCases(): array
    {
        return [
            // invalid auto spec params
            'it fails if object used as auto spec param' => [
                'query' => 'SELECT ?',
                'queryData' => [new \stdClass()],
                'expectedException' => TemplateParserException::invalidSpecParam(),
            ],

            // invalid int spec params
            'it fails if string used as int spec param' => [
                'query' => 'SELECT ?d',
                'queryData' => ['string'],
                'expectedException' => TemplateParserException::invalidSpecParam(),
            ],
            'it fails if array using used as int spec param' => [
                'query' => 'SELECT ?d',
                'queryData' => [['test']],
                'expectedException' => TemplateParserException::invalidSpecParam(),
            ],

            // invalid float spec params
            'it fails if string used as float spec param' => [
                'query' => 'SELECT ?f',
                'queryData' => ['string'],
                'expectedException' => TemplateParserException::invalidSpecParam(),
            ],
            'it fails if array using used as float spec param' => [
                'query' => 'SELECT ?f',
                'queryData' => [['test']],
                'expectedException' => TemplateParserException::invalidSpecParam(),
            ],
        ];
    }

    #[DataProvider('conditionalErrors')]
    public function test_it_throws_if_conditional_errors(
        string $query,
        array $queryData,
        TemplateParserException $expectedException,
    ): void
    {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        $this->templateParser->parse($query, $queryData);
    }

    public static function conditionalErrors(): array
    {
        return [
            'throws if closing bracket is missing' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d',
                'queryData' => [1],
                'expectedException' => new TemplateParserException('closing bracket is missing'),
            ],
            'throws if open bracket is missing' => [
                'query' => 'SELECT name FROM users WHERE id = 1 AND block = ?d}',
                'queryData' => [1],
                'expectedException' => new TemplateParserException('open bracket is missing'),
            ],
            'throws if placeholder in conditional is missing' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = d}',
                'queryData' => [1],
                'expectedException' => new TemplateParserException('parameter in conditional is missing'),
            ],
            'throws if placeholder data is missing' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d}',
                'queryData' => [],
                'expectedException' => new TemplateParserException('parameter data in conditional is missing'),
            ],
            'throws if nested conditionals' => [
                'query' => 'SELECT name FROM users WHERE id = 1{ AND block = ?d {AND user_id = ?d}}',
                'queryData' => ['user_id', [1, 2, 3], true],
                'expectedException' => new TemplateParserException('nested conditionals are not supported'),
            ],
        ];
    }
}
