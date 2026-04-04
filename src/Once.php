<?php

declare(strict_types=1);

namespace Thesis\Sync;

use Amp\Cancellation;
use Amp\Future;
use function Amp\async;

/**
 * @api
 *
 * @template-covariant T
 */
final class Once
{
    /**
     * @var ?Future<void>
     */
    private ?Future $state;

    /**
     * @var T
     * @phpstan-ignore property.uninitializedReadonly
     */
    private readonly mixed $value;

    /**
     * @phpstan-ignore property.uninitializedReadonly
     */
    private readonly \Throwable $error;

    /**
     * @param-later-invoked-callable $function
     * @param callable(): T $function
     */
    public function __construct(callable $function)
    {
        $weakThis = \WeakReference::create($this);

        /** @phpstan-ignore assign.propertyType */
        $this->state = async(static function () use ($function, $weakThis): void {
            $once = $weakThis->get();

            if ($once === null) {
                return;
            }

            try {
                $once->value = $function();
            } catch (\Throwable $error) {
                $once->error = $error;
            } finally {
                $once->state = null;
            }
        });
    }

    /**
     * @return T
     */
    public function await(?Cancellation $cancellation = null): mixed
    {
        $this->state?->await($cancellation);

        if (isset($this->error)) {
            throw $this->error;
        }

        return $this->value;
    }
}
