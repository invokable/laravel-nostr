# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel Nostr (`revolution/laravel-nostr`) is a PHP Laravel package that integrates Nostr protocol functionality into Laravel applications. It provides both high-level social networking features and low-level protocol access for decentralized, censorship-resistant communication.

## Core Commands

### Testing
```bash
# Run main test suite (feature tests with HTTP mocking)
vendor/bin/phpunit

# Run integration tests (real WebSocket connections - reliable in GitHub Actions, may fail in other restricted environments)
vendor/bin/phpunit --testsuite=Integration

# Run a specific test
vendor/bin/phpunit tests/Feature/Client/Native/ClientEventTest.php
```

### Code Quality
```bash
# Format code with Laravel Pint
vendor/bin/pint

# Check formatting without changes
vendor/bin/pint --test
```

### Laravel Package Development
```bash
# Publish config for testing
php artisan vendor:publish --tag=nostr-config

# Install package in development
composer require revolution/laravel-nostr
```

## Architecture Overview

### Driver Architecture
The package uses Laravel's Manager pattern with two client implementations:
- **Native Driver** (`src/Client/Native/`): Pure PHP implementation with WebSocket support
- **Node Driver** (`src/Client/Node/`): Delegates to external Node.js service

Driver selection is configured in `config/nostr.php` with 'native' as default.

### Core Systems

**1. High-Level Social API**
- `SocialClient` (`src/Social/SocialClient.php`): Main interface for social operations
- `Social` facade: Static access requiring manual registration
- Operations: `createNote()`, `updateProfile()`, `timeline()`, `follows()`

**2. Laravel Integration**
- `NostrServiceProvider`: Registers services and WebSocket HTTP mixin
- `NostrChannel`: Laravel notification channel for Nostr messages  
- `Nostr` facade: Low-level protocol access

**3. WebSocket Implementation**
- `WebSocketHttpMixin`: Extends Laravel's HTTP client with `Http::ws()` method
- `NativeWebSocket`: Wraps `valtzu/guzzle-websocket-middleware` for Nostr protocol
- Message types: `PublishEventMessage`, `RequestEventMessage`
- Handles REQ, EVENT, EOSE, OK, NOTICE protocol messages

**4. Protocol Layer**
- `Event`: Core Nostr event structure (id, pubkey, sig, kind, content, tags, created_at)
- `Filter`: Query criteria for event retrieval
- `Profile`: User profile representation
- `Kind`: Event type enumeration (Text=1, Metadata=0, Contacts=3, etc.)

**5. NIP Implementations**
- NIP-04: Encrypted direct messages (`NodeNip04`)
- NIP-05: DNS identity verification (`NativeNip05`)
- NIP-19: Bech32 entity encoding (`NativeNip19`, `NodeNip19`)
- NIP-17: Private direct messages (`NativeNip17`)

### Key Entry Points

**Social Operations:**
```php
// Via service injection (recommended)
app(SocialClient::class)->createNote('Hello Nostr!');
app(SocialClient::class)->timeline();

// Via facade (requires manual registration)
Social::driver('native')->updateProfile($profile);
```

**Laravel Notifications:**
```php
$user->notify(new NostrNotification('Message'));
Notification::route('nostr', NostrRoute::to(sk: 'secret_key'))
    ->notify(new NostrNotification('Message'));
```

**Low-Level Protocol:**
```php
Nostr::event()->list($filter);
Nostr::native()->event()->publish($event, $secretKey);
Nostr::key()->generate();
Nostr::nip19()->decode($nprofile);
```

## WebSocket Integration Details

The package extends Laravel's HTTP client with WebSocket capabilities:

- **WebSocketHttpMixin** registers `Http::ws(string $url, callable $callback)` globally
- **NativeWebSocket** provides Nostr-specific operations: `publish()`, `request()`, `list()`, `get()`
- Uses `valtzu/guzzle-websocket-middleware` for WebSocket protocol support
- Handles HTTP fakes gracefully during testing
- Default timeout: 60 seconds with automatic connection cleanup

## Testing Strategy

**Feature Tests**: Use Laravel's HTTP fake system to mock WebSocket operations
**Integration Tests**: Make real WebSocket connections (separate test suite)
**HTTP Fake Support**: WebSocket operations fallback to GET requests during testing

Integration tests work reliably in GitHub Actions environments. They may fail in other restricted environments (like GitHub Copilot) due to WebSocket connectivity limitations - this is expected in those environments.

## Important File Locations

- **Configuration**: `config/nostr.php` (driver selection, relay list, API base)
- **Service Provider**: `src/Providers/NostrServiceProvider.php`
- **Manager**: `src/NostrManager.php` (driver instantiation)
- **WebSocket Extension**: `src/Client/Native/WebSocketHttpMixin.php`
- **Test Config**: `phpunit.xml` (separates integration tests)
- **Code Style**: `pint.json` (Laravel preset, unused imports allowed)

## NIP-19 Response Format Consideration

`NativeNip19` uses different response formats:
- **Decode operations**: Return `{type: string, data: mixed}` format
- **Encode operations**: Return legacy `{[entityType]: string}` format (e.g., `{note: 'note1...'}`)

## Development Notes

- Package uses PSR-4 autoloading with `Revolution\Nostr\` namespace
- Laravel service provider auto-discovery enabled
- Comprehensive tag system with 17+ tag types in `src/Tags/`
- Secret keys marked with `#[SensitiveParameter]` attribute
- Default relay: `wss://relay.nostr.band` (18+ relays configured)