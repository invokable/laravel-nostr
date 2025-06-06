Basic client
====

## Drivers

### node
Depends on an [external WebAPI](https://github.com/kawax/nostr-vercel-api) running on node.js.

### native
Native client that works only PHP. Using [nostr-php](https://github.com/nostrver-se/nostr-php).

nip04 is not supported.

### Default driver can be set in `config/nostr.php` or `.env`
```php
// config/nostr.php

'driver' => env('NOSTR_DRIVER', 'node'),
```

```
// .env

NOSTR_DRIVER=native
```

If you do not specify a driver, the default will be used.

```php
use Revolution\Nostr\Facades\Nostr;

Nostr::event()->list();
```

You can also specify the driver explicitly.

```php
use Revolution\Nostr\Facades\Nostr;

Nostr::driver('node')->event()->list();
Nostr::node()->event()->list();

Nostr::driver('native')->event()->list();
Nostr::native()->event()->list();
```

## Which relay servers are used?

When only one server(`Nostr::event()`), the first of the relay in `config/nostr.php` is used.

When using `Nostr::pool()`, multiple servers are used. All relays in `config/nostr.php`.

## Change relay server at runtime

```php
use Revolution\Nostr\Facades\Nostr;

$response = Nostr::event()->withRelay('wss://')->...;
```

```php
use Revolution\Nostr\Facades\Nostr;

$response = Nostr::pool()->withRelays(['wss://', 'wss://'])->...;
```

## Generate new keys

```php
use Revolution\Nostr\Facades\Nostr;
use Illuminate\Http\Client\Response;

/** @var Response $response */
$response = Nostr::key()->generate();
// $response is Laravel HTTP client response.
$keys = $response->json();
//[
//    'sk' => 'sk...',
//    'nsec' => 'nsec...',
//    'pk' => 'pk...',
//    'npub' => 'npub...',
//]
```

## Convert keys

```php
use Revolution\Nostr\Facades\Nostr;

$response = Nostr::key()->fromNsec(nsec: 'nsec');
$keys = $response->json();
//[
//    'sk' => 'sk...',
//    'nsec' => 'nsec...',
//    'pk' => 'pk...',
//    'npub' => 'npub...',
//]
```

```php
use Revolution\Nostr\Facades\Nostr;

$response = Nostr::key()->fromSecretKey(sk: 'sk');
$keys = $response->json();
//[
//    'sk' => 'sk...',
//    'nsec' => 'nsec...',
//    'pk' => 'pk...',
//    'npub' => 'npub...',
//]
```

```php
use Revolution\Nostr\Facades\Nostr;

$response = Nostr::key()->fromNpub(npub: 'npub');
$keys = $response->json();
//[
//    'pk' => 'pk...',
//    'npub' => 'npub...',
//]
```

```php
use Revolution\Nostr\Facades\Nostr;

$response = Nostr::key()->fromPublicKey(pk: 'pk');
$keys = $response->json();
//[
//    'pk' => 'pk...',
//    'npub' => 'npub...',
//]
```

## Get multiple event list

```php
use Illuminate\Http\Client\Response;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;

// Get my recent notes
$filter = Filter::make(
            authors: ['my pk'],
            kinds: [Kind::Text],
            limit: 10,
        );

/** @var Response $response */
$response = Nostr::event()->list(filter: $filter);
$events = $response->json('events');
// [
//     [
//         'id' => '...1',
//         'kind' => 1,
//         'content' => '...',
//     ],
//     [
//         'id' => '...2',
//         'kind' => 1,
//         'content' => '...',
//     ],
// ]
```

## Get one event

```php
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;

// Get my profile
$filter = Filter::make(
            authors: ['my pk'],
            kinds: [Kind::Metadata],
        );

$response = Nostr::event()->get(filter: $filter);
$event = $response->json('event');
//[
//     'id' => '...',
//     'kind' => 0,
//     'content' => '{name: ""}',
//]
```

## Publish event
```php
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Event;
use Revolution\Nostr\Kind;

// Create new note
$event = Event::make(
            kind: Kind::Text,
            content: 'hello',
            created_at: now()->timestamp,
            tags: [],
        );

// secret key. required.
$sk = 'my sk';

$response = Nostr::event()->publish(event: $event, sk: $sk);

if($response->successful()) {
    // The signed event is also included in the response.
    $event = $response->json('event');
}
```

### Publish event to multiple relays
```php
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Event;
use Revolution\Nostr\Kind;

$event = Event::make(
            kind: Kind::Text,
            content: 'test',
            created_at: now()->timestamp,
            tags: [],
        );

$sk = 'my sk';

$responses = Nostr::pool()->publish(event: $event, sk: $sk);
// $responses is array<string, Response>
// [
//     'wss://relay1' => $response,
//     'wss://relay2' => $response,
// ]

foreach ($responses as $relay => $response) {
    if ($response->failed()) {
        dump($relay.' : '.$response->body());
    }
}
```

## Get NIP-05 profile

```php
use Revolution\Nostr\Facades\Nostr;

$profile = Nostr::nip05()->profile('user@localhost');
// $profile is array
//[
//    'user' => 'user@localhost',
//    'pubkey' => 'pk',
//    'relays' => [],
//]
```
