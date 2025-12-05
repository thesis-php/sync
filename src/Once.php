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
     * @phpstan-ignore property.uninitialized
     */
    private mixed $value;

    private bool $isResolved = false;

    /**
     * @param \Closure(): T $function
     * @param ?\Closure(T): bool $isAlive
     */
    public function __construct(
        private \Closure $function,
        private readonly ?\Closure $isAlive = null,
    ) {}

    /**
     * @return T
     */
    public function await(?Cancellation $cancellation = null): mixed
    {
        if ($this->isResolved && ($this->isAlive === null || ($this->isAlive)($this->value))) {
            return $this->value;
        }

        $this->isResolved = false;

        $this->future ??= async($this->function);

        try {
            $this->value = $this->future->await($cancellation);
        } finally {
            $this->future = null;
        }

        $this->isResolved = true;

        if ($this->isAlive === null) {
            $this->function = static fn() => throw new \LogicException('Function has been freed from memory');
        }

        return $this->value;
    }
}
