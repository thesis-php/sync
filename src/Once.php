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
     * @var ?T
     */
    private mixed $value = null;

    /**
     * @var callable(T): bool
     */
    private readonly mixed $isAlive;

    /**
     * @param callable(): T $factory
     * @param ?callable(T): bool $isAlive
     */
    public function __construct(
        private readonly mixed $factory,
        mixed $isAlive = null,
    ) {
        $this->isAlive = $isAlive ?? static fn(): true => true;
    }

    /**
     * @return T
     */
    public function __invoke(?Cancellation $cancellation = null): mixed
    {
        return $this->resolve($cancellation);
    }

    /**
     * @return T
     */
    public function resolve(?Cancellation $cancellation = null): mixed
    {
        if ($this->value !== null && ($this->isAlive)($this->value)) {
            return $this->value;
        }

        $this->future ??= async($this->factory);

        try {
            return $this->value = $this->future->await($cancellation);
        } finally {
            $this->future = null;
        }
    }
}
