<?php

declare(strict_types=1);

namespace Tez\Utils\Fn;

/**
 * Pre-fills the leading arguments of a callable and returns a new Closure
 * that accepts the remaining arguments.
 */
final class Partial
{
    /**
     * Returns a Closure with $partial pre-filled as the leftmost arguments.
     * The returned Closure forwards any additional arguments to $fn.
     */
    public static function apply(callable $fn, mixed ...$partial): \Closure
    {
        return static function () use ($fn, $partial): mixed {
            return $fn(...$partial, ...func_get_args());
        };
    }
}
