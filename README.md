Laravel Nostr
====
[![test](https://github.com/kawax/laravel-nostr/actions/workflows/test.yml/badge.svg)](https://github.com/kawax/laravel-nostr/actions/workflows/test.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/02a199563014d2dd8aca/maintainability)](https://codeclimate.com/github/kawax/laravel-nostr/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/02a199563014d2dd8aca/test_coverage)](https://codeclimate.com/github/kawax/laravel-nostr/test_coverage)

**Work in progress**

## Requirements
- PHP >= 8.2
- Laravel >= 11.0

## Installation

```shell
composer require revolution/laravel-nostr

php artisan vendor:publish --tag=nostr-config
```

### Uninstall
```shell
composer remove revolution/laravel-nostr
```

- Delete `config/nostr.php`

## Usage
- [NostrClient](./docs/nostr-client.md)
- [Laravel Notifications](./docs/notification.md)

## LICENCE
MIT
