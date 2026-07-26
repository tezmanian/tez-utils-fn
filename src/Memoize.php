<?php

declare(strict_types=1);

namespace Tez\Utils\Fn;

/**
 * Wraps a callable and caches its return value per unique argument signature.
 * Null return values are cached correctly and do not trigger a second invocation.
 *
 * Note: argument and return types are intentionally untyped — a variadic memoizer
 * cannot express the wrapped callable's full signature at the type-system level.
 */
final class Memoize
{
    /** @var callable $fn */
    private $fn;

    /** @var array<string, mixed> */
    private array $cache = [];

    /**  */
    private function __construct(callable $fn)
    {
        $this->fn = $fn;
    }

    public static function wrap(callable $fn): self
    {
        return new self($fn);
    }

    public function __invoke(mixed ...$args): mixed
    {
        $key = serialize($args);

        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = ($this->fn)(...$args);
        }

        return $this->cache[$key];
    }

    public function flush(): void
    {
        $this->cache = [];
    }
}
