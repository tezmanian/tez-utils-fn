<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Tests\Fn;

use PHPUnit\Framework\TestCase;
use Tezmanian\Utils\Fn\Once;

final class OnceTest extends TestCase
{
    public function testCallableIsExecutedOnFirstCall(): void
    {
        $calls  = 0;
        $once   = Once::wrap(function () use (&$calls): int {
            $calls++;

            return 42;
        });

        $result = $once();

        self::assertSame(42, $result);
        self::assertSame(1, $calls);
    }

    public function testCallableIsNotExecutedAgainOnSubsequentCalls(): void
    {
        $calls = 0;
        $once  = Once::wrap(function () use (&$calls): int {
            $calls++;

            return 42;
        });

        $once();
        $once();
        $once();

        self::assertSame(1, $calls);
    }

    public function testCachedValueIsReturnedOnSubsequentCalls(): void
    {
        $once = Once::wrap(fn(): string => 'hello');

        self::assertSame('hello', $once());
        self::assertSame('hello', $once()); // @phpstan-ignore staticMethod.alreadyNarrowedType
    }

    public function testNullReturnIsCachedCorrectly(): void
    {
        $calls = 0;
        $once  = Once::wrap(function () use (&$calls): ?string {
            $calls++;

            return null;
        });

        self::assertNull($once());
        self::assertNull($once());
        self::assertSame(1, $calls);
    }

    public function testEachInstanceIsIndependent(): void
    {
        $calls = 0;
        $fn    = function () use (&$calls): int {
            return ++$calls;
        };

        $a = Once::wrap($fn);
        $b = Once::wrap($fn);

        self::assertSame(1, $a());
        self::assertSame(2, $b());
        self::assertSame(1, $a());
        self::assertSame(2, $b());
        self::assertSame(2, $calls);
    }
}
