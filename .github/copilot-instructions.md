# Laravel Nostr Integration Package - Onboarding Guide

## Overview

This is a PHP Laravel package that integrates Nostr protocol functionality into Laravel applications. Nostr is a decentralized protocol for censorship-resistant social networking that operates over relays instead of centralized servers.

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

## Project Organization

### Core Systems

The package is organized into several key layers:

**1. High-Level Social API** (`src/Social/`)
- `SocialClient` - Main interface for social networking operations
- `Social` facade - Static access to SocialClient functionality

**2. Laravel Integration** (`src/Providers/`, `src/Facades/`, `src/Notifications/`)
- `NostrServiceProvider` - Package registration and configuration
- `NostrChannel` - Laravel notification channel for Nostr messages
- WebSocket HTTP client extensions

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
│   └── Node/         # External service implementation
├── Notifications/    # Laravel notification integration
├── Facades/          # Laravel facades
├── Providers/        # Service providers
├── Contracts/        # Interfaces and contracts
├── Tags/            # Event metadata system
├── Nip19/           # NIP-19 pointer structures
├── Event.php        # Core event data structure
├── Filter.php       # Event query criteria
├── Profile.php      # User profile data
└── Kind.php         # Event type enumeration

tests/               # Test suite
config/             # Package configuration
.github/workflows/  # CI/CD automation
```

### Key Entry Points

**For Social Features**:
```php
// Via facade
Social::createNote('Hello Nostr!');
Social::timeline();

// Via service injection
app(SocialClient::class)->updateProfile($profile);
```

**For Laravel Notifications**:
```php
$user->notify(new NostrNotification('Message content'));
```

**For Low-Level Protocol Access**:
```php
Nostr::driver('native')->event()->publish($event, $secretKey);
Nostr::native()->nip19()->decode($nprofile);
```

### Configuration

**Driver Selection** (`config/nostr.php`):
- `native`: Pure PHP implementation
- `node`: External Node.js service

**Relay Configuration**:
- Default relay list for event publishing/retrieval
- Per-operation relay override capability

### Development Practices

**Testing**: Comprehensive feature tests with HTTP mocking for external services

**Code Quality**:
- Laravel Pint for code formatting (`.github/workflows/lint.yml`)
- PHPUnit testing pipeline
- Automated quality analysis

**CI/CD**: GitHub Actions for testing across PHP versions and code quality checks

## Glossary of Codebase-Specific Terms

**SocialClient** - `src/Social/SocialClient.php` - Main high-level API for social operations like createNote(), updateProfile(), timeline()

**NostrDriver** - `src/Contracts/NostrDriver.php` - Interface defining contract for client implementations (native vs node)

**Event** - `src/Event.php` - Core Nostr data structure with id, pubkey, sig, kind, content, tags, created_at

**Kind** - `src/Kind.php` - Enum defining event types (Text=1, Metadata=0, Contacts=3, Reaction=7, etc.)

**Filter** - `src/Filter.php` - Query criteria object with ids, authors, kinds, since, until, limit parameters

**Profile** - `src/Profile.php` - User profile data with name, about, picture, nip05, lud16 fields

**NostrChannel** - `src/Notifications/NostrChannel.php` - Laravel notification channel for sending Nostr messages

**NostrMessage** - `src/Notifications/NostrMessage.php` - Notification payload with content, kind, tags

**NostrRoute** - `src/Notifications/NostrRoute.php` - Notification routing info with secret key and relays

**NativeClient** - `src/Client/Native/NativeClient.php` - Pure PHP implementation of NostrDriver interface

**NodeClient** - `src/Client/Node/NodeClient.php` - Implementation delegating to external Node.js service

**NativeWebSocket** - `src/Client/Native/NativeWebSocket.php` - WebSocket client for direct relay communication

**Relay** - Nostr server (wss://relay.url) that stores and distributes events across the network

**NIP-04** - `src/Client/Node/NodeNip04.php` - Encrypted direct message specification implementation

**NIP-05** - `src/Client/Native/NativeNip05.php` - DNS-based identity verification (user@domain.com format)

**NIP-19** - Bech32 encoding for Nostr entities (nsec, npub, note, nprofile, nevent, naddr)

**EventTag** - `src/Tags/EventTag.php` - Reference to another event with format ['e', id, relay, marker]

**PersonTag** - Reference to a user's public key, used in follows and mentions

**HashTag** - Content categorization tag for events (similar to Twitter hashtags)

**Timeline** - Chronological feed of events from followed users, created by SocialClient::timeline()

**Notes** - Text-based events (Kind::Text) representing posts or messages

**Follows** - List of public keys a user subscribes to, managed via SocialClient::follows()

**Reactions** - Events expressing sentiment toward other events (likes, dislikes)

**Reply** - Response event to another event, using EventTag to link parent and root

**Secret Key (sk)** - Private cryptographic key for signing events, marked #[SensitiveParameter]

**Public Key (pk)** - Derived from secret key, used to identify users and verify signatures

**NSEC/NPUB** - NIP-19 encoded forms of secret/public keys for human-readable sharing

**WebSocketHttpMixin** - `src/Client/Native/WebSocketHttpMixin.php` - Laravel HTTP client extension for WebSocket support

**HasHttp** - Trait providing HTTP client functionality for Node implementations
