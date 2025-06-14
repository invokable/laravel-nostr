# Laravel Nostr Integration Package - Onboarding Guide

## Overview

This is a PHP Laravel package (`revolution/laravel-nostr`) from the `invokable/laravel-nostr` repository that integrates Nostr protocol functionality into Laravel applications. Nostr is a decentralized protocol for censorship-resistant social networking that operates over relays instead of centralized servers.

**Purpose**: Enable Laravel applications to interact with the Nostr ecosystem, providing both high-level social networking features and low-level protocol access.

**Target Users**:
- Laravel developers building decentralized social applications
- Applications needing censorship-resistant messaging capabilities
- Developers integrating with the broader Nostr ecosystem

**Key Capabilities**:
- **Social Operations**: Create profiles, publish notes, manage follows, build timelines
- **Laravel Integration**: Send notifications via Nostr, use familiar Laravel patterns
- **Protocol Flexibility**: Choose between native PHP implementation or external Node.js service
- **WebSocket Communication**: Real-time interaction with Nostr relays
- **Cryptographic Operations**: Key generation, event signing, verification

**Requirements**:
- PHP 8.2+ with GMP extension
- Laravel 11.28+ or 12.0+
- Dependencies: `swentel/nostr-php`, `valtzu/guzzle-websocket-middleware`

## Project Organization

### Core Systems

The package is organized into several key layers:

**1. High-Level Social API** (`src/Social/`)
- `SocialClient` - Main interface for social networking operations
- `Social` facade - Static access to SocialClient functionality

**2. Laravel Integration** (`src/Providers/`, `src/Facades/`, `src/Notifications/`)
- `NostrServiceProvider` - Package registration and configuration
- `NostrChannel` - Laravel notification channel for Nostr messages
- WebSocket HTTP client extensions via `WebSocketHttpMixin`

**3. Core Protocol Layer** (`src/`)
- `Event` - Fundamental Nostr event data structure
- `Filter` - Query criteria for retrieving events
- `Profile` - User profile representation
- `Kind` - Enumeration of event types

**4. Client Implementations** (`src/Client/`)
- `NativeClient` - Pure PHP implementation
- `NodeClient` - Delegates to external Node.js service
- `NativeWebSocket` - WebSocket communication layer

**5. Protocol Specifications** (`src/Client/`, `src/Nip19/`)
- NIP-04: Encrypted direct messages
- NIP-05: DNS-based identity verification
- NIP-19: Bech32 entity encoding

### Directory Structure

```
src/
├── Social/           # High-level social networking API
│   └── SocialClient.php
├── Client/           # Protocol client implementations
│   ├── Native/       # Pure PHP implementation
│   │   └── Concerns/ # Shared traits and mixins
│   └── Node/         # External service implementation
│       └── Concerns/ # Shared traits and mixins
├── Notifications/    # Laravel notification integration
├── Facades/          # Laravel facades (Nostr, Social)
├── Providers/        # Service providers (NostrServiceProvider)
├── Contracts/        # Interfaces and contracts
│   └── Client/       # Client-specific contracts
├── Tags/            # Event metadata system (17+ tag types)
├── Nip19/           # NIP-19 pointer structures
├── Message/         # WebSocket message structures
├── Exceptions/      # Package-specific exceptions
├── Event.php        # Core event data structure
├── Filter.php       # Event query criteria
├── Profile.php      # User profile data
├── Kind.php         # Event type enumeration
└── NostrManager.php # Main manager class

tests/               # Test suite (140+ tests)
├── Feature/         # Feature tests with HTTP mocking
│   ├── Client/      # Client implementation tests
│   ├── Social/      # Social API tests
│   ├── Notifications/ # Notification channel tests
│   └── Tag/         # Tag system tests
└── TestCase.php     # Base test case

config/             # Package configuration
docs/               # Documentation files
.github/workflows/  # CI/CD automation (test, lint)
```

### Key Entry Points

**For Social Features**:
```php
// Via Social facade (requires manual registration)
Social::driver('native')->createNote('Hello Nostr!');
Social::driver('native')->timeline();

// Via service injection (recommended)
app(SocialClient::class)->updateProfile($profile);
app(SocialClient::class)->createNote('Hello Nostr!');
```

**For Laravel Notifications**:
```php
$user->notify(new NostrNotification('Message content'));

// On-demand notifications
Notification::route('nostr', NostrRoute::to(sk: 'secret_key'))
    ->notify(new NostrNotification('Message'));
```

**For Low-Level Protocol Access**:
```php
// Event operations
Nostr::event()->list();
Nostr::driver('native')->event()->publish($event, $secretKey);
Nostr::native()->event()->get($eventId);

// Key operations
Nostr::key()->generate();
Nostr::native()->key()->fromNsec($nsec);

// NIP implementations
Nostr::nip05()->profile('user@domain.com');
Nostr::nip19()->decode($nprofile);
```

### WebSocket Integration

The package provides seamless WebSocket connectivity for real-time communication with Nostr relays through Laravel's familiar HTTP client interface.

**Architecture**:
- **External Dependency**: Uses `valtzu/guzzle-websocket-middleware` for WebSocket protocol support
- **Laravel Integration**: Extends Laravel's HTTP client with WebSocket capabilities via mixin
- **Native Implementation**: Pure PHP WebSocket handling without external Node.js dependencies

**Core Components**:

**WebSocketHttpMixin** (`src/Client/Native/WebSocketHttpMixin.php`):
- Extends `Illuminate\Http\Client\PendingRequest` with WebSocket functionality
- Registered globally in `NostrServiceProvider::websocket()` method
- Provides `Http::ws(string $url, callable $callback): mixed` method
- Handles WebSocket handshake (HTTP 101 status code) and connection management
- Supports both synchronous and asynchronous (Promise-based) operations
- Gracefully handles HTTP fakes during testing

**NativeWebSocket** (`src/Client/Native/NativeWebSocket.php`):
- Wraps `Valtzu\WebSocketMiddleware\WebSocketStream` for Nostr-specific operations
- Implements Nostr protocol message handling (EVENT, REQ, EOSE, OK, NOTICE)
- Provides high-level methods: `publish()`, `request()`, `list()`, `get()`
- Manages connection timeouts (default: 60 seconds)
- Automatically closes connections after operations
- Uses Laravel's `rescue()` helper for graceful error handling

**Message Structures**:
- **PublishEventMessage** (`src/Message/PublishEventMessage.php`): Formats EVENT messages for publishing
- **RequestEventMessage** (`src/Message/RequestEventMessage.php`): Formats REQ messages for event retrieval

**Usage Patterns**:

```php
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Client\Native\NativeWebSocket;

// Basic WebSocket connection
$result = Http::ws('wss://relay.nostr.band', function (NativeWebSocket $ws) {
    // Perform operations with the WebSocket connection
    return $ws->list($filter);
});

// Publishing events via WebSocket
Http::ws($relay, function (NativeWebSocket $ws) use ($event, $secretKey) {
    return $ws->publish($event, $secretKey);
});

// Retrieving events with filters
Http::ws($relay, function (NativeWebSocket $ws) use ($filter) {
    return $ws->request($filter);
});
```

**Integration with Native Client**:
The `NativeEvent` class (`src/Client/Native/NativeEvent.php`) uses WebSocket connections for all relay operations:

```php
// Event publishing
public function publish(Event $event, string $sk, ?string $relay = null): Response
{
    $relay = $relay ?? $this->relay;
    
    $response = Http::ws($relay, fn (NativeWebSocket $ws) => 
        $this->response($ws->publish($event, $sk))
    );
    
    return $this->publishResponse($response);
}

// Event retrieval
public function list(Filter $filter, ?string $relay = null): Response
{
    $relay = $relay ?? $this->relay;
    
    return Http::ws($relay, fn (NativeWebSocket $ws) => 
        $this->response(['events' => $ws->list($filter)])
    );
}
```

**Testing Support**:
- WebSocket operations are mockable through Laravel's HTTP fake system
- Integration tests demonstrate real WebSocket connections (`tests/Integration/`)
- Test failures in restricted environments (like GitHub Copilot) are expected and should be ignored
- Feature tests use HTTP mocking to test WebSocket logic without network dependencies

**Benefits**:
- **Familiar API**: Uses Laravel's `Http::` facade syntax that developers already know
- **Real-time Communication**: Direct WebSocket connections to Nostr relays
- **Pure PHP**: No external Node.js service required for WebSocket operations
- **Testable**: Full support for HTTP fakes and mocking during testing
- **Reliable**: Automatic connection management, timeouts, and error handling

### Configuration

**Driver Selection** (`config/nostr.php`):
- `native`: Pure PHP implementation (default)
- `node`: External Node.js service

**API Configuration**:
- `api_base`: Node.js service endpoint (default: https://nostr-api.vercel.app/api/)

**Relay Configuration**:
- Default relay list for event publishing/retrieval (18+ relays including Japanese relays)
- Per-operation relay override capability
- Primary relay is first in the list (`wss://relay.nostr.band`)

### Development Practices

**Testing**: Comprehensive feature tests with HTTP mocking for external services (140+ tests, PHPUnit)

**Code Quality**:
- Laravel Pint for code formatting
- PHP 8.4 support in CI/CD pipeline
- Automated quality analysis

**CI/CD**: GitHub Actions for testing across PHP versions and code quality checks

**Package Structure**:
- PSR-4 autoloading (`Revolution\Nostr\` namespace)
- Laravel service provider auto-discovery
- MIT license

## Integration Tests

**Integration Tests with Real WebSocket Connections**: The package includes integration tests in `tests/Integration/` that connect to actual Nostr relays using real WebSocket connections. These tests validate the complete WebSocket integration stack including the `Http::ws()` method, `NativeWebSocket` operations, and relay communication protocols.

**WebSocket Testing Strategy**:
- **Unit/Feature Tests**: Use Laravel's HTTP fake system to mock WebSocket operations
- **Integration Tests**: Make real WebSocket connections to test network communication
- **HTTP Fake Support**: `WebSocketHttpMixin` gracefully handles `Http::fake()` during testing by falling back to GET requests

**Running Integration Tests**:
- Execute via: `vendor/bin/phpunit --testsuite=Integration`
- These tests are separate from the main test suite and excluded from regular test runs
- **In Copilot Environment**: Integration test failures should be ignored as they require real network access to WebSocket servers
- Tests include WebSocket connection validation and real Nostr protocol operations

**Example Integration Test Usage**:
```php
// Real WebSocket connection test
Http::timeout(5)->ws('wss://relay.nostr.band', function ($ws) {
    return ['status' => 'connected'];
});
```

**CI/CD Integration**: On GitHub Actions, integration tests are executed in `.github/workflows/test-integration.yml`. While working in Copilot, you may be able to check results from this workflow to verify integration test status.

**Test Separation**: The phpunit.xml configuration separates integration tests from regular tests:
- Main test suite: `tests/Feature/` and `tests/Unit/` (149+ tests)
- Integration test suite: `tests/Integration/` (real WebSocket connections)

## Glossary of Codebase-Specific Terms

**NostrManager** - `src/NostrManager.php` - Laravel Manager class extending Illuminate\Support\Manager, handles driver selection and instantiation

**SocialClient** - `src/Social/SocialClient.php` - Main high-level API for social operations like createNote(), updateProfile(), timeline()

**NostrDriver** - `src/Contracts/NostrDriver.php` - Interface defining contract for client implementations (native vs node)

**Event** - `src/Event.php` - Core Nostr data structure with id, pubkey, sig, kind, content, tags, created_at

**Kind** - `src/Kind.php` - Enum defining event types (Text=1, Metadata=0, Contacts=3, EncryptedDirectMessage=4, Reaction=7, etc.)

**Filter** - `src/Filter.php` - Query criteria object with ids, authors, kinds, since, until, limit parameters

**Profile** - `src/Profile.php` - User profile data with name, about, picture, nip05, lud16 fields

**NostrChannel** - `src/Notifications/NostrChannel.php` - Laravel notification channel for sending Nostr messages

**NostrMessage** - `src/Notifications/NostrMessage.php` - Notification payload with content, kind, tags

**NostrRoute** - `src/Notifications/NostrRoute.php` - Notification routing info with secret key and relays

**NativeClient** - `src/Client/Native/NativeClient.php` - Pure PHP implementation of NostrDriver interface

**NodeClient** - `src/Client/Node/NodeClient.php` - Implementation delegating to external Node.js service

**NativeWebSocket** - `src/Client/Native/NativeWebSocket.php` - WebSocket client for direct relay communication

**WebSocketHttpMixin** - `src/Client/Native/WebSocketHttpMixin.php` - Laravel HTTP client extension providing `Http::ws()` method, registered as mixin in NostrServiceProvider, handles WebSocket handshake and connection lifecycle

**NativeWebSocket** - `src/Client/Native/NativeWebSocket.php` - WebSocket client wrapper for `WebSocketStream`, provides Nostr protocol operations (publish, request, list, get), manages timeouts and connection cleanup

**WebSocketStream** - From `valtzu/guzzle-websocket-middleware` package, low-level WebSocket connection stream used by NativeWebSocket for read/write operations

**PublishEventMessage** - `src/Message/PublishEventMessage.php` - WebSocket message structure for EVENT commands, formats Nostr events for publishing to relays

**RequestEventMessage** - `src/Message/RequestEventMessage.php` - WebSocket message structure for REQ commands, formats event filters for retrieval from relays with subscription ID

**ClientEvent** - `src/Contracts/Client/ClientEvent.php` - Contract for event operations (list, publish, get)

**ClientKey** - `src/Contracts/Client/ClientKey.php` - Contract for key operations (generate, convert)

**ClientPool** - `src/Contracts/Client/ClientPool.php` - Contract for connection pooling operations

**Relay** - Nostr server (wss://relay.url) that stores and distributes events across the network, accessed via WebSocket connections

**WebSocket Handshake** - HTTP upgrade process (status code 101) that establishes WebSocket connection from initial HTTP request, handled automatically by WebSocketHttpMixin

**EOSE (End of Stored Events)** - Nostr relay response indicating all stored events matching a subscription have been sent, processed by NativeWebSocket to terminate event retrieval

**REQ Message** - WebSocket message type for requesting events from relays, formatted as `["REQ", subscription_id, filter...]`

**EVENT Message** - WebSocket message type for publishing events to relays, formatted as `["EVENT", event_object]`

**OK Message** - Relay response to EVENT messages indicating acceptance/rejection, processed by NativeWebSocket for publish confirmation

**NOTICE Message** - Relay informational/error messages, handled by NativeWebSocket for error reporting

**NIP-04** - `src/Client/Node/NodeNip04.php` - Encrypted direct message specification implementation

**NIP-05** - `src/Client/Native/NativeNip05.php` - DNS-based identity verification (user@domain.com format)

**NIP-19** - Bech32 encoding for Nostr entities (nsec, npub, note, nprofile, nevent, naddr)

**NativeNip19 Response Formats** - The NativeNip19 implementation uses different response formats for decode vs encode operations:
- **Decode methods** (`decode()`, `decode_note()`): Return `{type: string, data: mixed}` format to match NodeNip19 specification
- **Encode methods** (`note()`, `nprofile()`, `nevent()`, `naddr()`): Return legacy format `{[entityType]: string}` where entityType is the specific type (e.g., `{note: 'note1...'}`, `{nprofile: 'nprofile1...'}`)
- **Key consideration**: Package uses `Illuminate\Http\Client\Response` but actual JSON structure varies by operation type

**EventTag** - `src/Tags/EventTag.php` - Reference to another event with format ['e', id, relay, marker]

**PersonTag** - `src/Tags/PersonTag.php` - Reference to a user's public key, used in follows and mentions

**HashTag** - `src/Tags/HashTag.php` - Content categorization tag for events (#hashtag format)

**AddressTag** - `src/Tags/AddressTag.php` - Reference to replaceable events by coordinate

**IdentityTag** - `src/Tags/IdentityTag.php` - NIP-05 identity verification tag

**RelayTag** - `src/Tags/RelayTag.php` - Relay recommendation tag

**SubjectTag** - `src/Tags/SubjectTag.php` - Message subject/title tag

**ImageTag** - `src/Tags/ImageTag.php` - Image attachment tag

**Timeline** - Chronological feed of events from followed users, created by SocialClient::timeline()

**Notes** - Text-based events (Kind::Text) representing posts or messages

**Follows** - List of public keys a user subscribes to, managed via SocialClient::follows()

**Reactions** - Events expressing sentiment toward other events (likes, dislikes)

**Reply** - Response event to another event, using EventTag to link parent and root

**Secret Key (sk)** - Private cryptographic key for signing events, marked #[SensitiveParameter]

**Public Key (pk)** - Derived from secret key, used to identify users and verify signatures

**NSEC/NPUB** - NIP-19 encoded forms of secret/public keys for human-readable sharing

**ProfilePointer** - `src/Nip19/ProfilePointer.php` - NIP-19 profile pointer structure (nprofile)

**EventPointer** - `src/Nip19/EventPointer.php` - NIP-19 event pointer structure (nevent)

**AddressPointer** - `src/Nip19/AddressPointer.php` - NIP-19 address pointer structure (naddr)

**PublishEventMessage** - `src/Message/PublishEventMessage.php` - WebSocket message for publishing events

**RequestEventMessage** - `src/Message/RequestEventMessage.php` - WebSocket message for requesting events

**EventNotFoundException** - `src/Exceptions/EventNotFoundException.php` - Exception for missing events
