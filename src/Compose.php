<?php

declare(strict_types=1);

namespace Tez\Utils\Fn;

/**
 * Combines callables using right-to-left mathematical composition (f∘g∘h).
 * The rightmost callable is applied first — the mathematical counterpart to Pipeline.
 *
 * Compose::build(f, g, h) is equivalent to Pipeline::build(h, g, f).
 * An empty composition returns the value unchanged.
 */
final class Compose
{
    /**
     * Applies $fns to $value right-to-left immediately and returns the result.
     */
    public static function apply(mixed $value, callable ...$fns): mixed
    {
        foreach (array_reverse($fns) as $fn) {
            $value = $fn($value);
        }

        return $value;
    }

    /**
     * Builds a reusable right-to-left composition closure.
     * The returned Closure is compatible with Memoize::wrap() and Retry::run().
     */
    public static function build(callable ...$fns): \Closure
    {
        $reversed = array_reverse($fns);

        return static function (mixed $value) use ($reversed): mixed {
            foreach ($reversed as $fn) {
                $value = $fn($value);
            }

            return $value;
        };
    }
}
