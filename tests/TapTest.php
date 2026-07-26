<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Fn;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Fn\Tap;

final class TapTest extends TestCase
{
    public function testReturnsOriginalValue(): void
    {
        $result = Tap::value('hello', fn($v) => strtoupper($v));

        self::assertSame('hello', $result);
    }

    public function testCallableSideEffectIsExecuted(): void
    {
        $called = false;

        Tap::value('anything', function () use (&$called): void {
            $called = true;
        });

        self::assertTrue($called);
    }

    public function testCallableReceivesTheValue(): void
    {
        $received = null;

        Tap::value(42, function (int $v) use (&$received): void {
            $received = $v;
        });

        self::assertSame(42, $received);
    }

    public function testReturnValueOfCallableIsDiscarded(): void
    {
        $result = Tap::value('original', fn() => 'ignored');

        self::assertSame('original', $result);
    }

    public function testWorksWithObjects(): void
    {
        $obj    = new \stdClass();
        $result = Tap::value($obj, fn($v) => null);

        self::assertSame($obj, $result);
    }

    public function testWorksWithNull(): void
    {
        $result = Tap::value(null, fn($v) => 'ignored');

        self::assertNull($result);
    }
}
