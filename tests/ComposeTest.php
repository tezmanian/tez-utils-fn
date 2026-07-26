<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Fn;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Fn\Compose;

final class ComposeTest extends TestCase
{
    // -------------------------------------------------------------------------
    // apply()
    // -------------------------------------------------------------------------

    public function testApplyExecutesRightToLeft(): void
    {
        $result = Compose::apply(
            -3,
            fn(int $n) => $n * 2,   // applied third: 4 * 2 = 8
            fn(int $n) => $n + 1,   // applied second: 3 + 1 = 4
            fn(int $n) => abs($n),  // applied first:  abs(-3) = 3
        );

        self::assertSame(8, $result);
    }

    public function testApplyWithNoFnsReturnsValueUnchanged(): void
    {
        self::assertSame(42, Compose::apply(42));
    }

    public function testApplyWithSingleFn(): void
    {
        self::assertSame('HELLO', Compose::apply('hello', 'strtoupper'));
    }

    public function testApplyRightToLeftOrderWithStrings(): void
    {
        // trim first, then strtolower, then replace spaces
        $result = Compose::apply(
            '  Hello World  ',
            fn(string $s) => str_replace(' ', '-', $s),
            'strtolower',
            'trim',
        );

        self::assertSame('hello-world', $result);
    }

    // -------------------------------------------------------------------------
    // build()
    // -------------------------------------------------------------------------

    public function testBuildReturnsReusableClosure(): void
    {
        $process = Compose::build(
            fn(string $s) => str_replace(' ', '-', $s),
            'strtolower',
            'trim',
        );

        self::assertSame('hello-world', $process('  Hello World  '));
        self::assertSame('foo-bar', $process('  FOO BAR  '));
    }

    public function testBuildWithNoFnsReturnsValueUnchanged(): void
    {
        $fn = Compose::build();

        self::assertSame('hello', $fn('hello'));
    }

    public function testBuildReturnsClosure(): void
    {
        self::assertInstanceOf(\Closure::class, Compose::build('trim'));
    }

    public function testBuildIsEquivalentToPipelineWithReversedStages(): void
    {
        $double    = fn(int $n) => $n * 2;
        $increment = fn(int $n) => $n + 1;
        $absolute  = fn(int $n) => abs($n);

        $composed = Compose::build($double, $increment, $absolute);

        self::assertSame(8, $composed(-3));
    }
}
