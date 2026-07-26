<?php

declare(strict_types=1);

namespace Tez\Utils\Fn;

final class Retry
{
    /**
     * Executes a callable, retrying on exception up to $maxAttempts times.
     *
     * @template TReturn
     * @param callable(): TReturn $fn
     * @param list<class-string<\Throwable>> $only Retry only for these exception types (empty = any)
     * @param list<class-string<\Throwable>> $except Never retry for these exception types
     *
     * @throws \Throwable the last exception when all attempts are exhausted
     * @return TReturn
     */
    public static function run(
        callable $fn,
        int $maxAttempts = 3,
        int $backoffMs = 0,
        bool $exponential = false,
        array $only = [],
        array $except = [],
    ): mixed {
        $attempt   = 0;
        $lastError = null;

        while ($attempt < $maxAttempts) {
            try {
                return $fn();
            } catch (\Throwable $e) {
                $lastError = $e;

                if (!self::shouldRetry($e, $only, $except)) {
                    throw $e;
                }

                $attempt++;

                if ($attempt < $maxAttempts && $backoffMs > 0) {
                    $delay = $exponential
                        ? $backoffMs * (2 ** ($attempt - 1))
                        : $backoffMs * $attempt;

                    usleep($delay * 1000);
                }
            }
        }

        throw $lastError ?? new \RuntimeException('Retry failed without exception.');
    }

    /**
     * Determines whether the given exception should trigger a retry.
     * Returns false immediately if $only is set and the exception does not match,
     * or if the exception matches any entry in $except.
     *
     * @param list<class-string<\Throwable>> $only
     * @param list<class-string<\Throwable>> $except
     */
    private static function shouldRetry(\Throwable $e, array $only, array $except): bool
    {
        if ($only !== []) {
            $matchesOnly = false;

            foreach ($only as $type) {
                if ($e instanceof $type) {
                    $matchesOnly = true;
                    break;
                }
            }

            if (!$matchesOnly) {
                return false;
            }
        }

        foreach ($except as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }
}
