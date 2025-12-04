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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.15
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== revolution/laravel-boost-copilot-cli rules ===

## Laravel Boost for GitHub Copilot CLI

### MCP Configuration File Required
- If you cannot see the `laravel-boost` MCP server or tools, the user has likely forgotten to specify the MCP configuration file when starting Copilot CLI.
- Instruct the user to restart Copilot CLI with the correct command:
  ```
  copilot --additional-mcp-config @.github/mcp-config.json --continue
  ```
- The `--additional-mcp-config` option is **required** for every Copilot CLI session to access Laravel Boost MCP tools.

### Laravel Package Development Environment
- This is a **Laravel package development project** using Orchestra Testbench, not a standard Laravel application.
- The environment differs significantly from a typical Laravel project - there is no full application context, database, or application-specific models.
- **Important:** Not all Laravel Boost MCP tools will work correctly in this environment:
  - Tools that depend on database connections, specific models, application routes, or other application-specific features may not be available or may fail.
  - Tools like `database-query`, `database-schema`, `list-routes` may return limited or no results.
  - Basic tools like `application-info`, `list-artisan-commands`, `search-docs` should work normally.
- Focus on package-specific development tasks: writing tests, implementing package features, and ensuring compatibility with Laravel.
- Use `vendor/bin/testbench` commands instead of `php artisan` when needed.
</laravel-boost-guidelines>
