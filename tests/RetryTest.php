<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Fn;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Fn\Retry;

final class RetryTest extends TestCase
{
    public function testSucceedsOnFirstAttempt(): void
    {
        $calls  = 0;
        $result = Retry::run(function () use (&$calls): string {
            $calls++;

            return 'ok';
        });

        self::assertSame('ok', $result);
        self::assertSame(1, $calls);
    }

    public function testRetriesAndSucceedsOnSecondAttempt(): void
    {
        $calls = 0;
        $result = Retry::run(function () use (&$calls): string {
            $calls++;

            if ($calls < 2) {
                throw new \RuntimeException('fail');
            }

            return 'ok';
        }, maxAttempts: 3);

        self::assertSame('ok', $result);
        self::assertSame(2, $calls);
    }

    public function testThrowsLastExceptionAfterAllAttempts(): void
    {
        $calls = 0;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('attempt 3');

        Retry::run(function () use (&$calls): never {
            $calls++;

            throw new \RuntimeException('attempt ' . $calls);
        }, maxAttempts: 3);
    }

    public function testMaxAttemptsIsRespected(): void
    {
        $calls = 0;

        try {
            Retry::run(function () use (&$calls): never {
                $calls++;

                throw new \RuntimeException();
            }, maxAttempts: 4);
        } catch (\RuntimeException) {
        }

        self::assertSame(4, $calls);
    }

    public function testOnlyRetiesOnMatchingExceptionType(): void
    {
        $calls = 0;

        $result = Retry::run(function () use (&$calls): string {
            $calls++;

            if ($calls === 1) {
                throw new \InvalidArgumentException('retry me');
            }

            return 'ok';
        }, maxAttempts: 3, only: [\InvalidArgumentException::class]);

        self::assertSame('ok', $result);
        self::assertSame(2, $calls);
    }

    public function testOnlyThrowsImmediatelyForNonMatchingType(): void
    {
        $this->expectException(\RuntimeException::class);

        Retry::run(function (): never {
            throw new \RuntimeException('not in only list');
        }, maxAttempts: 3, only: [\InvalidArgumentException::class]);
    }

    public function testExceptThrowsImmediatelyForMatchingType(): void
    {
        $calls = 0;

        $this->expectException(\InvalidArgumentException::class);

        Retry::run(function () use (&$calls): never {
            $calls++;

            throw new \InvalidArgumentException('stop immediately');
        }, maxAttempts: 3, except: [\InvalidArgumentException::class]);
    }

    public function testExceptAllowsRetryForNonMatchingType(): void
    {
        $calls = 0;

        $result = Retry::run(function () use (&$calls): string {
            $calls++;

            if ($calls === 1) {
                throw new \RuntimeException('retry ok');
            }

            return 'ok';
        }, maxAttempts: 3, except: [\InvalidArgumentException::class]);

        self::assertSame('ok', $result);
        self::assertSame(2, $calls);
    }

    public function testBackoffWithZeroDoesNotSleep(): void
    {
        // Just verify it runs correctly without any sleep delay
        $calls  = 0;
        $result = Retry::run(function () use (&$calls): string {
            $calls++;

            if ($calls < 3) {
                throw new \RuntimeException();
            }

            return 'done';
        }, maxAttempts: 3, backoffMs: 0);

        self::assertSame('done', $result);
    }

    public function testSubclassMatchesOnlyFilter(): void
    {
        $calls  = 0;
        $result = Retry::run(function () use (&$calls): string {
            $calls++;

            if ($calls === 1) {
                // LogicException extends \Exception which extends \Throwable
                throw new \LogicException('subclass');
            }

            return 'ok';
        }, maxAttempts: 3, only: [\Exception::class]);

        self::assertSame('ok', $result);
    }
}
