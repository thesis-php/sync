<?php

declare(strict_types=1);

namespace Thesis\Sync;

/**
 * @template T
 * @param callable(): T $factory
 * @param ?callable(T): bool $isAlive
 * @return Once<T>
 */
function once(callable $factory, ?callable $isAlive = null): Once
{
    return new Once($factory, $isAlive);
}
