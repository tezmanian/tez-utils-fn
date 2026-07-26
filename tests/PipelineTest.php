<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Fn;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Fn\Pipeline;

final class PipelineTest extends TestCase
{
    // -------------------------------------------------------------------------
    // process()
    // -------------------------------------------------------------------------

    public function testProcessPassesValueThroughStages(): void
    {
        $result = Pipeline::process(
            '  Hello World  ',
            'trim',
            'strtolower',
            fn(string $s) => str_replace(' ', '-', $s),
        );

        self::assertSame('hello-world', $result);
    }

    public function testProcessWithNoStagesReturnsPayloadUnchanged(): void
    {
        self::assertSame(42, Pipeline::process(42));
    }

    public function testProcessWithSingleStage(): void
    {
        self::assertSame('HELLO', Pipeline::process('hello', 'strtoupper'));
    }

    public function testProcessPassesReturnValueOfEachStageToNext(): void
    {
        $log = [];

        Pipeline::process(
            1,
            function (int $v) use (&$log): int {
                $log[] = $v;

                return $v + 1;
            },
            function (int $v) use (&$log): int {
                $log[] = $v;

                return $v + 1;
            },
            function (int $v) use (&$log): int {
                $log[] = $v;

                return $v + 1;
            },
        );

        self::assertSame([1, 2, 3], $log);
    }

    // -------------------------------------------------------------------------
    // build()
    // -------------------------------------------------------------------------

    public function testBuildReturnsReusableClosure(): void
    {
        $normalize = Pipeline::build(
            'trim',
            'strtolower',
            fn(string $s) => preg_replace('/\s+/', ' ', $s) ?? '',
        );

        self::assertSame('hello world', $normalize('  Hello   World  '));
        self::assertSame('foo bar', $normalize('  FOO  BAR  '));
    }

    public function testBuildWithNoStagesReturnsPayloadUnchanged(): void
    {
        $pipe = Pipeline::build();

        self::assertSame('hello', $pipe('hello'));
    }

    public function testBuildReturnsClosure(): void
    {
        self::assertInstanceOf(\Closure::class, Pipeline::build('trim'));
    }
}
