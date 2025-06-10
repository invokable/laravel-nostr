Laravel Nostr
====
[![Maintainability](https://qlty.sh/badges/1b16feb1-672b-4289-8011-3d0c007381b9/maintainability.svg)](https://qlty.sh/gh/invokable/projects/laravel-nostr)
[![Code Coverage](https://qlty.sh/badges/1b16feb1-672b-4289-8011-3d0c007381b9/test_coverage.svg)](https://qlty.sh/gh/invokable/projects/laravel-nostr)

[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/invokable/laravel-nostr)

## Work in progress

Because the Nostr specifications are still evolving, this package is under constant development. However, the notification features should be useful as-is.

## Requirements
- PHP(+GMP) >= 8.2
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
