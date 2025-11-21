<?php

declare(strict_types=1);

namespace Thesis\Sync;

use Amp\Cancellation;
use Amp\Future;
use function Amp\async;

/**
 * @api
 * @template T
 */
final class Once
{
    /**
     * @var ?Future<T>
     */
    private ?Future $future = null;

    /**
     * @var T
     */
    private mixed $value;

    /**
     * @var \Closure(T): bool
     */
    private readonly mixed $isAlive;

    private bool $isResolved = false;

    /**
     * @param \Closure(): T $function
     * @param ?\Closure(T): bool $isAlive
     */
    public function __construct(
        private readonly \Closure $function,
        ?\Closure $isAlive = null,
    ) {
        $this->isAlive = $isAlive ?? static fn(): true => true;
    }

    /**
     * @return T
     */
    public function await(?Cancellation $cancellation = null): mixed
    {
        if ($this->isResolved && ($this->isAlive)($this->value)) {
            return $this->value;
        }

        $this->future ??= async($this->function);

        try {
            $this->value = $this->future->await($cancellation);
        } finally {
            $this->future = null;
        }

        $this->isResolved = true;

        return $this->value;
    }
}
