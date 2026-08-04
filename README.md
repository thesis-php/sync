# Thesis Sync Once

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/thesis/sync-once/php)](https://packagist.org/packages/thesis/sync-once)
[![GitHub Release](https://img.shields.io/github/v/release/thesis-php/once-value)](https://github.com/thesis-php/once-value/releases)
[![Code Coverage](https://codecov.io/gh/thesis-php/once-value/branch/0.2.x/graph/badge.svg)](https://codecov.io/gh/thesis-php/once-value/tree/0.2.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fthesis-php%2Fonce-value%2F0.2.x)](https://dashboard.stryker-mutator.io/reports/github.com/thesis-php/once-value/0.2.x)

`Once` starts a function once, shares the same running operation between awaiters,
and memoizes either the returned value or the thrown exception.

## Installation

```shell
composer require thesis/sync-once
```

## Usage

```php
use Amp\TimeoutCancellation;
use Thesis\Amqp\Channel;
use Thesis\Amqp\Client;
use Thesis\Amqp\Message;
use Thesis\Sync\Once;

final readonly class AmqpTransport
{
    /**
     * @var Once<Channel>
     */
    private Once $publishChannel;

    public function __construct(
        private Client $client,
    ) {
        $this->publishChannel = new Once(
            // make sure to use static closures to avoid circular references
            function: static fn (): Channel => $client->channel(),
        );
    }

    public function publish(Message $message): void
    {
        $this
            ->publishChannel
            ->await(new TimeoutCancellation(10))
            ->publish($message);
    }
}
```
