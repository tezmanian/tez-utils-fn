<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Fn;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Fn\Partial;

final class PartialTest extends TestCase
{
    public function testPrefillsSingleArgument(): void
    {
        $multiply = fn(int $a, int $b): int => $a * $b;

        $double = Partial::apply($multiply, 2);
        $triple = Partial::apply($multiply, 3);

        self::assertSame(10, $double(5));
        self::assertSame(15, $triple(5));
    }

    public function testPrefillsMultipleArguments(): void
    {
        $fn = fn(int $a, int $b, int $c): int => $a + $b + $c;

        $addFive = Partial::apply($fn, 2, 3);

        self::assertSame(15, $addFive(10));
    }

    public function testReturnsClosure(): void
    {
        $fn = Partial::apply('strtoupper');

        self::assertInstanceOf(\Closure::class, $fn);
    }

    public function testWorksWithBuiltinFunctions(): void
    {
        $implodeWithComma = Partial::apply('implode', ', ');

        self::assertSame('a, b, c', $implodeWithComma(['a', 'b', 'c']));
    }

    public function testWorksWithNoRemainingArguments(): void
    {
        $greet = fn(string $greeting, string $name): string => "{$greeting}, {$name}!";

        $sayHello = Partial::apply($greet, 'Hello', 'World');

        self::assertSame('Hello, World!', $sayHello());
    }

    public function testPrefillsNoArguments(): void
    {
        $fn    = fn(int $n): int => $n * 2;
        $wrapped = Partial::apply($fn);

        self::assertSame(10, $wrapped(5));
    }

    public function testComposesWithPipeline(): void
    {
        $addPrefix = Partial::apply(fn(string $prefix, string $s) => $prefix . $s, '>>> ');

        $pipeline = \Tez\Utils\Fn\Pipeline::build(
            'strtoupper',
            $addPrefix,
        );

        self::assertSame('>>> HELLO', $pipeline('hello'));
    }
}
