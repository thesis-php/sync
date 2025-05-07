# Thesis Sync Once

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/thesis/sync/php)](https://packagist.org/packages/thesis/sync)
[![GitHub Release](https://img.shields.io/github/v/release/thesis-php/sync)](https://github.com/thesis-php/sync/releases)
[![Code Coverage](https://codecov.io/gh/thesis-php/sync/branch/0.1.x/graph/badge.svg)](https://codecov.io/gh/thesis-php/sync/tree/0.1.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fthesis-php%2Fsync%2F0.1.x)](https://dashboard.stryker-mutator.io/reports/github.com/thesis-php/sync/0.1.x)

## Installation

```shell
composer require thesis/sync-once
```

## Usage

```php
use Amp\TimeoutCancellation;
use Thesis\Sync\Once;

final readonly class Message {}

final readonly class Client
{
    public function channel(): Channel {}
}

final readonly class Channel
{
    public function isClosed(): bool {}

    public function publish(Message $message): void {}
}

final readonly class Transport
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
            isAlive: static fn (Channel $channel): bool => !$channel->isClosed(),
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
