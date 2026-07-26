<?php

declare(strict_types=1);

namespace Tez\Utils\Fn;

/**
 * Executes a side-effect callable on a value and returns the value unchanged.
 * The return value of $fn is intentionally discarded.
 */
final class Tap
{
    /**
     * Calls $fn($value) for its side-effect, then returns $value unchanged.
     *
     * @template T
     * @param T $value
     * @return T
     */
    public static function value(mixed $value, callable $fn): mixed
    {
        $fn($value);

        return $value;
    }
}
