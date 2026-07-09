# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] 2026-07-09

### Removed

- `Once::__construct()` no longer accepts the `$isAlive` parameter. The value cannot be invalidated and recalculated anymore.
- `Once::await()` no longer throws `LogicException('Function has been freed from memory')`. The function is released automatically once it completes.

### Changed

- The function is now invoked eagerly in the constructor instead of lazily on the first `Once::await()` call. Constructing `Once` requires a running event loop.
- An exception thrown by the function is now memoized and rethrown on every `Once::await()` call. Previously the function was reexecuted on the next call.
- Cancelling `Once::await()` no longer discards the running function: the `Cancellation` only aborts that particular await.
- `Once::__construct()` accepts any `callable` instead of a `\Closure`.
- Bumped the minimum PHP version to 8.4.

## [0.1.3] 2025-11-21

### Changed 

- Free function from memory if `$isAlive` is null

## [0.1.2] 2025-11-21

### Fixed

- Support `null` values

## [0.1.1] 2025-05-07

### Changed

- Rename `Once::$factory` to `Once::$function`
