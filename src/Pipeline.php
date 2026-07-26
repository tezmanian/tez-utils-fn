<?php

declare(strict_types=1);

namespace Tez\Utils\Fn;

/**
 * Pipes a value through an ordered sequence of callables left-to-right.
 * Each stage receives the return value of the previous stage.
 * An empty pipeline returns the payload unchanged.
 */
final class Pipeline
{
    /**
     * Passes $payload through each stage immediately and returns the result.
     */
    public static function process(mixed $payload, callable ...$stages): mixed
    {
        foreach ($stages as $stage) {
            $payload = $stage($payload);
        }

        return $payload;
    }

    /**
     * Builds a reusable pipeline closure that can be invoked later.
     * The returned Closure is compatible with Memoize::wrap() and Retry::run().
     */
    public static function build(callable ...$stages): \Closure
    {
        return static function (mixed $payload) use ($stages): mixed {
            foreach ($stages as $stage) {
                $payload = $stage($payload);
            }

            return $payload;
        };
    }
}
