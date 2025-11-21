<?php

declare(strict_types=1);

namespace Thesis\Sync;

use Amp\DeferredFuture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function Amp\async;

#[CoversClass(Once::class)]
final class OnceTest extends TestCase
{
    public function testReturnsOnceSameValue(): void
    {
        /** @var DeferredFuture<null> */
        $deferred = new DeferredFuture();
        $once = new Once(static function () use ($deferred): string {
            $deferred->getFuture()->await();

            return random_bytes(8);
        });
        $future1 = async(static fn() => $once->await());
        $future2 = async(static fn() => $once->await());

        async(static function () use ($deferred, $future1, $future2): void {
            self::assertFalse($future1->isComplete());
            self::assertFalse($future2->isComplete());

            $deferred->complete();

            self::assertSame($future1->await(), $future2->await());
        })->await();
    }

    public function testWorksWithNull(): void
    {
        /** @var DeferredFuture<null> */
        $deferred = new DeferredFuture();
        $once = new Once(static fn(): null => $deferred->getFuture()->await());
        $future = async(static fn() => $once->await());

        async(static function () use ($deferred, $future): void {
            self::assertFalse($future->isComplete());

            $deferred->complete();

            self::assertNull($future->await());
        })->await();
    }

    public function testIsAlive(): void
    {
        $once = new Once(
            static function (): int {
                /** @var int */
                static $i = 0;

                return ++$i;
            },
            static fn(int $i): bool => $i > 1,
        );

        self::assertSame(1, $once->await());
        self::assertSame(2, $once->await());
        self::assertSame(2, $once->await());
        self::assertSame(2, $once->await());
    }
}
