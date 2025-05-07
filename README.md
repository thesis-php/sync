# Thesis Sync

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/thesis/sync/php)](https://packagist.org/packages/thesis/sync)
[![GitHub Release](https://img.shields.io/github/v/release/thesis-php/sync)](https://github.com/thesis-php/sync/releases)
[![Code Coverage](https://codecov.io/gh/thesis-php/sync/branch/0.1.x/graph/badge.svg)](https://codecov.io/gh/thesis-php/sync/tree/0.1.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fthesis-php%2Fsync%2F0.1.x)](https://dashboard.stryker-mutator.io/reports/github.com/thesis-php/sync/0.1.x)

## Installation

```shell
composer require thesis/sync
```

## Once

### Usage

```php
use Amp\TimeoutCancellation;
use Thesis\Sync\Once;

final readonly class Connection
{
    public function isAlive(): bool
    {
        // ...
    }

    public function query(string $query): mixed
    {
        // ...
    }
}

final readonly class Client
{
    /**
     * @var Once<Connection>
     */
    private Once $connection;

    public function __construct()
    {
        $this->connection = new Once(
            factory: $this->doConnect(...),
            isAlive: static fn (Connection $connection): bool => $connection->isAlive(),
        );
    }

    public function query(string $query): mixed
    {
        return $this
            ->connection
            ->await(new TimeoutCancellation(10))
            ->query($query);
    }

    private function doConnect(): Connection
    {
        // ...
    }
}
```
