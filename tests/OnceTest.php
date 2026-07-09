<?php

declare(strict_types=1);

namespace Thesis\Sync;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function Amp\async;
use function Amp\delay;
use function Amp\Future\await;
use function Amp\Future\awaitAll;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;

#[CoversClass(Once::class)]
final class OnceTest extends TestCase
{
    public function testItMemoizesValue(): void
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

        assertSame($value1, $value2);
    }

    public function testItMemoizesException(): void
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

        /** @phpstan-ignore deadCode.unreachable */
        assertSame($error1, $error2);
    }

    public function testItFreesFunctionWhenComplete(): void
    {
        $value = new \stdClass();
        $weakValue = \WeakReference::create($value);
        $once = new Once(static fn() => $value::class);
        unset($value);

        self::assertNotNull($weakValue->get());

        $once->await();

        self::assertNull($weakValue->get());
    }

    public function testItIsGarbageCollected(): void
    {
        $enabled = gc_enabled();

        if ($enabled) {
            gc_disable();
        }

        try {
            $weakOnce = \WeakReference::create(new Once(static fn() => true));

            assertNull($weakOnce->get());
        } finally {
            if ($enabled) {
                gc_enable();
            }
        }
    }
}
