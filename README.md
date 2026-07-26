# tez-utils-fn

Functional programming primitives for PHP: pipelines, composition, memoization, partial application, once-guards, retry with backoff, and tap for side-effects. PHP 8.3+, no dependencies.

## Installation

```bash
composer require tez/utils-fn
```

## Components

| Class | Purpose |
|-------|---------|
| `Pipeline` | Passes a value through callables left-to-right |
| `Compose` | Combines callables right-to-left (mathematical f∘g∘h) |
| `Memoize` | Caches return values per argument signature |
| `Once` | Executes a callable exactly once, returns cached result on subsequent calls |
| `Partial` | Pre-fills leading arguments of a callable |
| `Retry` | Retries a callable on exception with optional backoff and exception filtering |
| `Tap` | Executes a side-effect on a value and returns the value unchanged |

---

## Pipeline

Passes a value through an ordered sequence of callables left-to-right. Each stage receives the output of the previous one.

```php
use Tez\Utils\Fn\Pipeline;

// Immediate execution
Pipeline::process(
    '  Hello World  ',
    'trim',
    'strtolower',
    fn(string $s) => str_replace(' ', '-', $s),
);
// 'hello-world'

// Reusable closure
$normalize = Pipeline::build(
    'trim',
    'strtolower',
    fn(string $s) => preg_replace('/\s+/', ' ', $s) ?? '',
);

$normalize('  Hello   World  '); // 'hello world'
$normalize('  FOO  BAR  ');      // 'foo bar'
```

---

## Compose

Right-to-left composition — the mathematical counterpart to `Pipeline`. The rightmost callable is applied first.

`Compose::build(f, g, h)` is equivalent to `Pipeline::build(h, g, f)`.

```php
use Tez\Utils\Fn\Compose;

// Immediate execution — applies right-to-left: trim → strtolower → str_replace
Compose::apply(
    '  Hello World  ',
    fn(string $s) => str_replace(' ', '-', $s),
    'strtolower',
    'trim',
);
// 'hello-world'

// Reusable closure
$process = Compose::build(
    fn(string $s) => str_replace(' ', '-', $s),
    'strtolower',
    'trim',
);

$process('  Hello World  '); // 'hello-world'
$process('  FOO BAR  ');    // 'foo-bar'
```

---

## Memoize

Wraps a callable and caches its return value per unique argument signature. `null` return values are cached correctly and do not trigger a second invocation.

```php
use Tez\Utils\Fn\Memoize;

$expensiveCalc = Memoize::wrap(function (int $n): int {
    // called only once per unique $n
    return $n * $n;
});

$expensiveCalc(5);  // computed: 25
$expensiveCalc(5);  // from cache: 25
$expensiveCalc(6);  // computed: 36

// Multiple arguments form a unique cache key
$add = Memoize::wrap(fn(int $a, int $b): int => $a + $b);
$add(1, 2); // computed: 3
$add(2, 3); // computed: 5
$add(1, 2); // from cache: 3

// Clear the cache
$expensiveCalc->flush();
$expensiveCalc(5); // computed again
```

---

## Once

Executes a callable exactly once. Every subsequent call returns the cached return value without re-invoking the callable. Each `Once` instance is independent.

```php
use Tez\Utils\Fn\Once;

$init = Once::wrap(function (): string {
    // executed only on the first call
    return 'initialized';
});

$init(); // 'initialized' — callable is executed
$init(); // 'initialized' — returned from cache
$init(); // 'initialized' — returned from cache

// Null return values are cached correctly
$once = Once::wrap(fn(): ?string => null);
$once(); // null — callable executed once
$once(); // null — from cache, callable NOT called again
```

---

## Partial

Pre-fills the leading arguments of a callable and returns a new `Closure` that accepts the remaining arguments.

```php
use Tez\Utils\Fn\Partial;

$multiply = fn(int $a, int $b): int => $a * $b;

$double = Partial::apply($multiply, 2);
$triple = Partial::apply($multiply, 3);

$double(5);  // 10
$triple(5);  // 15

// Multiple pre-filled arguments
$fn     = fn(int $a, int $b, int $c): int => $a + $b + $c;
$addFive = Partial::apply($fn, 2, 3);
$addFive(10); // 15

// Works with built-in functions
$implodeWithComma = Partial::apply('implode', ', ');
$implodeWithComma(['a', 'b', 'c']); // 'a, b, c'

// Composes with Pipeline
$addPrefix = Partial::apply(fn(string $prefix, string $s) => $prefix . $s, '>>> ');
$pipeline  = Pipeline::build('strtoupper', $addPrefix);
$pipeline('hello'); // '>>> HELLO'
```

---

## Retry

Retries a callable on exception. Supports linear and exponential backoff, exception allowlists (`only`), and exception blocklists (`except`). Throws the last exception when all attempts are exhausted.

```php
use Tez\Utils\Fn\Retry;

// Basic retry — up to 3 attempts (default)
$result = Retry::run(fn() => fetchFromApi());

// Custom attempt count
Retry::run(fn() => fetchFromApi(), maxAttempts: 5);

// Linear backoff — 100 ms, 200 ms, 300 ms between attempts
Retry::run(fn() => fetchFromApi(), maxAttempts: 3, backoffMs: 100);

// Exponential backoff — 100 ms, 200 ms, 400 ms between attempts
Retry::run(fn() => fetchFromApi(), maxAttempts: 4, backoffMs: 100, exponential: true);

// Only retry on specific exception types
Retry::run(
    fn() => fetchFromApi(),
    maxAttempts: 3,
    only: [\RuntimeException::class],
);

// Never retry on specific exception types (throw immediately)
Retry::run(
    fn() => fetchFromApi(),
    maxAttempts: 3,
    except: [\InvalidArgumentException::class],
);
```

Subclass matching is supported — `only: [\Exception::class]` retries on any `\Exception` subclass.

---

## Tap

Executes a side-effect callable on a value and returns the value unchanged. The return value of the callable is discarded.

Useful for logging, debugging, or triggering side-effects inside a pipeline without breaking the chain.

```php
use Tez\Utils\Fn\Tap;
use Tez\Utils\Fn\Pipeline;

Tap::value('hello', fn($v) => strtoupper($v)); // 'hello' — callable return discarded

// Typical use: logging inside a pipeline
$pipeline = Pipeline::build(
    'trim',
    fn(string $s) => Tap::value($s, fn($v) => logger()->debug('after trim', ['v' => $v])),
    'strtolower',
);
```

---

## Requirements

- PHP 8.3+
- No runtime dependencies

## License

MIT
