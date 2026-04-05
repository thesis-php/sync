<?php

declare(strict_types=1);

namespace Thesis\Sync;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Amp\async;
use function Amp\delay;
use function Amp\Future\await;
use function Amp\Future\awaitAll;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

#[CoversClass(Once::class)]
#[CoversClass(LazyOnce::class)]
final class OnceTest extends TestCase
{
    /**
     * @param class-string<Once<*>|LazyOnce<*>> $class
     */
    #[DataProvider('provideClasses')]
    public function testItMemoizesValue(string $class): void
    {
        $once = new $class(static function (): string {
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

    /**
     * @param class-string<Once<*>|LazyOnce<*>> $class
     */
    #[DataProvider('provideClasses')]
    public function testItMemoizesException(string $class): void
    {
        $once = new $class(static function (): never {
            delay(0.01);

            throw new \RuntimeException(random_bytes(8));
        });

        /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
        [$error1, $error2] = awaitAll([
            async(static fn() => $once->await()),
            async(static fn() => $once->await()),
        ])[0];

        assertSame($error1, $error2);
    }

    /**
     * @param class-string<Once<*>|LazyOnce<*>> $class
     */
    #[DataProvider('provideClasses')]
    public function testItFreesFunctionWhenComplete(string $class): void
    {
        $value = new \stdClass();
        $weakValue = \WeakReference::create($value);
        $once = new $class(static fn() => $value::class);
        unset($value);

        self::assertNotNull($weakValue->get());

        $once->await();

        self::assertNull($weakValue->get());
    }

    /**
     * @param class-string<Once<*>|LazyOnce<*>> $class
     */
    #[DataProvider('provideClasses')]
    public function testItIsGarbageCollected(string $class): void
    {
        $enabled = gc_enabled();

        if ($enabled) {
            gc_disable();
        }

        try {
            $weakOnce = \WeakReference::create(new $class(static fn() => true));

            assertNull($weakOnce->get());
        } finally {
            if ($enabled) {
                gc_enable();
            }
        }
    }

    /**
     * @return \Generator<array{class-string}>
     */
    public static function provideClasses(): iterable
    {
        yield [Once::class];
        yield [LazyOnce::class];
    }

    public function testLazyOnceIsLazy(): void
    {
        $called = false;
        $once = new LazyOnce(static function () use (&$called): void {
            $called = true;
        });

        assertFalse($called);

        $once->await();

        /** @phpstan-ignore function.impossibleType */
        assertTrue($called);
    }
}
