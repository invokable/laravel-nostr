<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasEvent;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientPool;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;

/**
 * Working with multiple relays.
 */
class NativePool implements ClientPool
{
    use Conditionable;
    use HasEvent;
    use HasHttp;
    use Macroable;

    protected array $relays = [];

    public function __construct()
    {
        $this->relays = Config::get('nostr.relays') ?? [];
    }

    /**
     * @param  array<string>  $relays
     */
    public function withRelays(array $relays): static
    {
        $this->relays = $relays;

        return $this;
    }

    /**
     * Publish event to multiple relays simultaneously.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Event;
     * use Revolution\Nostr\Kind;
     *
     * $event = new Event(kind: Kind::Text, content: 'Hello Nostr!');
     * $responses = Nostr::native()->pool()->publish($event, 'secret_key', ['wss://relay1.com', 'wss://relay2.com']);
     * // $responses is array<string, Response>
     * // [
     * //     'wss://relay1.com' => Response with ['message' => 'OK', 'id' => 'subscription_id'],
     * //     'wss://relay2.com' => Response with ['message' => 'OK', 'id' => 'subscription_id'],
     * // ]
     * foreach ($responses as $relay => $response) {
     *     if ($response->successful()) {
     *         $json = $response->json(); // ['message' => 'OK', 'id' => 'subscription_id']
     *     }
     * }
     * ```
     *
     * @param  Event  $event  The event to publish
     * @param  string  $sk  Secret key for signing the event
     * @param  array<string>  $relays  Array of relay URLs (optional, uses default if empty)
     * @return array<array-key, Response> Array of responses keyed by relay URL
     */
    public function publish(Event $event, #[\SensitiveParameter] string $sk, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        $responses = Http::pool(fn (Pool $pool) => collect($relays)
            ->map(fn ($relay) => $pool->as($relay)->ws($relay, function (NativeWebSocket $ws) use ($event, $sk) {
                return $this->response($ws->publish($event, $sk));
            }))
            ->toArray(),
        );

        return collect($responses)
            ->map(fn ($response) => $this->publishResponse($response))
            ->toArray();
    }

    /**
     * Get multiple events from multiple relays simultaneously.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Filter;
     * use Revolution\Nostr\Kind;
     *
     * $filter = Filter::make(authors: ['my_pubkey'], kinds: [Kind::Text], limit: 10);
     * $responses = Nostr::native()->pool()->list($filter, ['wss://relay1.com', 'wss://relay2.com']);
     * // $responses is array<string, Response>
     * // [
     * //     'wss://relay1.com' => Response with ['events' => [...]],
     * //     'wss://relay2.com' => Response with ['events' => [...]],
     * // ]
     * foreach ($responses as $relay => $response) {
     *     if ($response->successful()) {
     *         $events = $response->json('events'); // Array of event objects
     *     }
     * }
     * ```
     *
     * @param  Filter  $filter  Filter criteria for event selection
     * @param  array<string>  $relays  Array of relay URLs (optional, uses default if empty)
     * @return array<array-key, Response> Array of responses keyed by relay URL, each containing {events: array}
     */
    public function list(Filter $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(fn (Pool $pool) => collect($relays)
            ->map(fn ($relay) => $pool->as($relay)->ws($relay, function (NativeWebSocket $ws) use ($filter) {
                return $this->response(['events' => $ws->list($filter)]);
            }))
            ->toArray(),
        );
    }

    /**
     * Get a single event from multiple relays simultaneously.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Filter;
     * use Revolution\Nostr\Kind;
     *
     * $filter = Filter::make(authors: ['my_pubkey'], kinds: [Kind::Metadata]);
     * $responses = Nostr::native()->pool()->get($filter, ['wss://relay1.com', 'wss://relay2.com']);
     * // $responses is array<string, Response>
     * // [
     * //     'wss://relay1.com' => Response with ['event' => {...}],
     * //     'wss://relay2.com' => Response with ['event' => {...}],
     * // ]
     * foreach ($responses as $relay => $response) {
     *     if ($response->successful()) {
     *         $event = $response->json('event'); // Single event object or empty array
     *     }
     * }
     * ```
     *
     * @param  Filter  $filter  Filter criteria for event selection
     * @param  array<string>  $relays  Array of relay URLs (optional, uses default if empty)
     * @return array<array-key, Response> Array of responses keyed by relay URL, each containing {event: array}
     */
    public function get(Filter $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(fn (Pool $pool) => collect($relays)
            ->map(fn ($relay) => $pool->as($relay)->ws($relay, function (NativeWebSocket $ws) use ($filter) {
                return $this->response(['event' => $ws->get($filter)]);
            }))
            ->toArray(),
        );
    }
}
