<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Fn;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Fn\Memoize;

final class MemoizeTest extends TestCase
{
    public function testCallableIsExecutedOnFirstCall(): void
    {
        $calls    = 0;
        $memoized = Memoize::wrap(function (int $n) use (&$calls): int {
            $calls++;

            return $n * 2;
        });

        self::assertSame(84, $memoized(42));
        self::assertSame(1, $calls);
    }

    public function testSameArgumentsReturnCachedValue(): void
    {
        $calls    = 0;
        $memoized = Memoize::wrap(function (int $n) use (&$calls): int {
            $calls++;

            return $n * 2;
        });

        $memoized(42);
        $memoized(42);
        $memoized(42);

        self::assertSame(1, $calls);
    }

    public function testDifferentArgumentsAreComputedSeparately(): void
    {
        $calls    = 0;
        $memoized = Memoize::wrap(function (int $n) use (&$calls): int {
            $calls++;

            return $n * 2;
        });

        self::assertSame(84, $memoized(42));
        self::assertSame(198, $memoized(99));
        self::assertSame(2, $calls);
    }

    public function testNullReturnIsCachedCorrectly(): void
    {
        $calls    = 0;
        $memoized = Memoize::wrap(function () use (&$calls): ?string {
            $calls++;

            return null;
        });

        self::assertNull($memoized());
        self::assertNull($memoized());
        self::assertSame(1, $calls);
    }

    public function testFlushClearsCache(): void
    {
        $calls    = 0;
        $memoized = Memoize::wrap(function (int $n) use (&$calls): int {
            $calls++;

            return $n;
        });

        $memoized(1);
        $memoized->flush();
        $memoized(1);

        self::assertSame(2, $calls);
    }

    public function testFlushDoesNotAffectSubsequentCaching(): void
    {
        $calls    = 0;
        $memoized = Memoize::wrap(function (int $n) use (&$calls): int {
            $calls++;

            return $n;
        });

        $memoized(5);
        $memoized->flush();
        $memoized(5);
        $memoized(5);

        self::assertSame(2, $calls);
    }

    public function testMultipleArgumentsFormUniqueKey(): void
    {
        $calls    = 0;
        $memoized = Memoize::wrap(function (int $a, int $b) use (&$calls): int {
            $calls++;

            return $a + $b;
        });

        self::assertSame(3, $memoized(1, 2));
        self::assertSame(3, $memoized(1, 2));
        self::assertSame(5, $memoized(2, 3));
        self::assertSame(2, $calls);
    }
}
