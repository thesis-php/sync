<?php

declare(strict_types=1);

namespace Thesis\Sync;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;
use function Amp\async;
use function Amp\delay;
use function Amp\Future\await;
use function Amp\Future\awaitAll;

#[Test]
#[Covers(Once::class)]
final class OnceTest
{
    public function itMemoizesValue(): void
    {
        $once = new Once(static function (): string {
            delay(0.01);

            return random_bytes(8);
        });

        /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
        [$value1, $value2] = await([
            async(static fn() => $once->await()),
            async(static fn() => $once->await()),
        ]);

        Assert::same($value1, $value2);
    }

    public function itMemoizesException(): void
    {
        $once = new Once(static function (): never {
            delay(0.01);

            throw new \RuntimeException(random_bytes(8));
        });

        /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
        [$error1, $error2] = awaitAll([
            async(static fn() => $once->await()),
            async(static fn() => $once->await()),
        ])[0];

        Assert::same($error1, $error2);
    }

    public function itFreesFunctionWhenComplete(): void
    {
        $value = new \stdClass();
        $weakValue = \WeakReference::create($value);
        $once = new Once(static fn() => $value::class);
        unset($value);

        Assert::notNull($weakValue->get());

        $once->await();

        Assert::null($weakValue->get());
    }

    public function itIsGarbageCollected(): void
    {
        $enabled = gc_enabled();

        if ($enabled) {
            gc_disable();
        }

        try {
            $weakOnce = \WeakReference::create(new Once(static fn() => true));

            Assert::null($weakOnce->get());
        } finally {
            if ($enabled) {
                gc_enable();
            }
        }
    }
}
