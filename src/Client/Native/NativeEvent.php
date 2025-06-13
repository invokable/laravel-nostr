<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasEvent;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientEvent;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;

/**
 * Working with single relay.
 */
class NativeEvent implements ClientEvent
{
    use Conditionable;
    use HasEvent;
    use HasHttp;
    use Macroable;

    protected string $relay = '';

    public function __construct()
    {
        $this->relay = Config::get('nostr.relays.0') ?? '';
    }

    public function withRelay(string $relay): static
    {
        $this->relay = $relay;

        return $this;
    }

    /**
     * Publish event to a relay.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Event;
     * use Revolution\Nostr\Kind;
     *
     * $event = new Event(kind: Kind::Text, content: 'Hello Nostr!');
     * $response = Nostr::native()->event()->publish($event, 'secret_key', 'wss://relay.example.com');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'message' => 'OK',
     * //     'id' => 'subscription_id',
     * // ]
     * ```
     *
     * @param  Event  $event  The event to publish
     * @param  string  $sk  Secret key for signing the event
     * @param  string|null  $relay  Relay URL (optional, uses default if null)
     * @return \Illuminate\Http\Client\Response JSON: {message: string, id: string}
     */
    public function publish(Event $event, #[\SensitiveParameter] string $sk, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        $response = Http::ws($relay, fn (NativeWebSocket $ws) => $this->response($ws->publish($event, $sk)));

        return $this->publishResponse($response);
    }

    /**
     * Get multiple events from a relay.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Filter;
     * use Revolution\Nostr\Kind;
     *
     * $filter = Filter::make(authors: ['my_pubkey'], kinds: [Kind::Text], limit: 10);
     * $response = Nostr::native()->event()->list($filter, 'wss://relay.example.com');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'events' => [
     * //         [
     * //             'id' => 'event_id_1',
     * //             'kind' => 1,
     * //             'content' => 'Hello world',
     * //             'pubkey' => 'author_pubkey',
     * //             'created_at' => 1234567890,
     * //             // ... other event fields
     * //         ],
     * //         // ... more events
     * //     ]
     * // ]
     * ```
     *
     * @param  Filter  $filter  Filter criteria for event selection
     * @param  string|null  $relay  Relay URL (optional, uses default if null)
     * @return \Illuminate\Http\Client\Response JSON: {events: array}
     */
    public function list(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        return Http::ws($relay, fn (NativeWebSocket $ws) => $this->response(['events' => $ws->list($filter)]));
    }

    /**
     * Get a single event from a relay.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Filter;
     * use Revolution\Nostr\Kind;
     *
     * $filter = Filter::make(authors: ['my_pubkey'], kinds: [Kind::Metadata]);
     * $response = Nostr::native()->event()->get($filter, 'wss://relay.example.com');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'event' => [
     * //         'id' => 'event_id',
     * //         'kind' => 0,
     * //         'content' => '{"name": "My Name", "about": "About me"}',
     * //         'pubkey' => 'author_pubkey',
     * //         'created_at' => 1234567890,
     * //         // ... other event fields
     * //     ]
     * // ]
     * ```
     *
     * @param  Filter  $filter  Filter criteria for event selection
     * @param  string|null  $relay  Relay URL (optional, uses default if null)
     * @return \Illuminate\Http\Client\Response JSON: {event: array}
     */
    public function get(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        return Http::ws($relay, fn (NativeWebSocket $ws) => $this->response(['event' => $ws->get($filter)]));
    }

    /**
     * Generate hash for an event.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Event;
     * use Revolution\Nostr\Kind;
     *
     * $event = Event::make(kind: Kind::Text, content: 'Hello')->withPublicKey('pubkey');
     * $response = Nostr::native()->event()->hash($event);
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'hash' => 'abc123def456...',
     * // ]
     * ```
     *
     * @param  Event  $event  The event to hash
     * @return \Illuminate\Http\Client\Response JSON: {hash: string}
     */
    public function hash(Event $event): Response
    {
        return $this->response([
            'hash' => $event->hash(),
        ]);
    }

    /**
     * Sign an event with a secret key.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Event;
     * use Revolution\Nostr\Kind;
     *
     * $event = new Event(kind: Kind::Text, content: 'Hello Nostr!');
     * $response = Nostr::native()->event()->sign($event, 'secret_key');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'sign' => 'signature_hex_string...',
     * // ]
     * ```
     *
     * @param  Event  $event  The event to sign
     * @param  string  $sk  Secret key for signing
     * @return \Illuminate\Http\Client\Response JSON: {sign: string}
     */
    public function sign(Event $event, #[\SensitiveParameter] string $sk): Response
    {
        $signed_event = $event->sign($sk);

        return $this->response([
            'sign' => $signed_event->sig,
        ]);
    }

    /**
     * Verify the signature of an event.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Event;
     * use Revolution\Nostr\Kind;
     *
     * $event = Event::make(kind: Kind::Text, content: 'Hello')->sign('secret_key');
     * $response = Nostr::native()->event()->verify($event);
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'verify' => true,
     * // ]
     * ```
     *
     * @param  Event  $event  The event to verify
     * @return \Illuminate\Http\Client\Response JSON: {verify: bool}
     */
    public function verify(Event $event): Response
    {
        $n_event = $this->toNativeEvent($event);

        return $this->response([
            'verify' => $n_event->verify(),
        ]);
    }
}
