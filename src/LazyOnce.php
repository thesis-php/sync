<?php

declare(strict_types=1);

namespace Thesis\Sync;

use Amp\Cancellation;

/**
 * @api
 *
 * @template-covariant T
 */
final class LazyOnce
{
    /**
     * @var \Closure(): T|Once<T>
     */
    private \Closure|Once $state;

    /**
     * @param-later-invoked-callable $function
     * @param callable(): T $function
     */
    public function __construct(callable $function)
    {
        $this->state = $function(...);
    }

    /**
     * @return T
     */
    public function await(?Cancellation $cancellation = null): mixed
    {
        if ($this->state instanceof \Closure) {
            $this->state = new Once($this->state);
        }

        return $this->state->await($cancellation);
    }
}
