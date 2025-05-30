Laravel Nostr
====
[![test](https://github.com/kawax/laravel-nostr/actions/workflows/test.yml/badge.svg)](https://github.com/kawax/laravel-nostr/actions/workflows/test.yml)

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
- [Basic Client](./docs/basic-client.md)
- [Laravel Notifications](./docs/notification.md)

## LICENCE
MIT
