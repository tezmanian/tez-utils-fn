<?php

declare(strict_types=1);

namespace Tez\Utils\Fn;

/**
 * Wraps a callable so it is executed exactly once.
 * Every subsequent call returns the cached return value without re-invoking the callable.
 *
 * @template TReturn
 */
final class Once
{
    /** @var callable(): TReturn */
    private $fn;

    private bool $called = false;

    private mixed $result = null;

    /** @param callable(): TReturn $fn */
    private function __construct(callable $fn)
    {
        $this->fn = $fn;
    }

    /**
     * @template T
     * @param callable(): T $fn
     * @return self<T>
     */
    public static function wrap(callable $fn): self
    {
        return new self($fn);
    }

    /** @return TReturn */
    public function __invoke(): mixed
    {
        if (!$this->called) {
            $this->result = ($this->fn)();
            $this->called = true;
        }

        return $this->result;
    }
}
