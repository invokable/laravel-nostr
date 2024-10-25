<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasEvent;
use Revolution\Nostr\Client\Native\Concerns\HasFilter;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientPool;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\RelayResponse\RelayResponseEvent;

/**
 * Working with multiple relays.
 *
 * @todo
 */
class NativePool implements ClientPool
{
    use HasHttp;
    use HasEvent;
    use HasFilter;
    use Macroable;

    public function __construct(
        protected array $relays = [],
    ) {
        $this->relays = Config::get('nostr.relays', []);
    }

    /**
     * @param  array<string>  $relays
     */
    public function withRelays(array $relays): static
    {
        $this->relays = $relays;

        return $this;
    }

    public function publish(Event|array $event, #[\SensitiveParameter] string $sk, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        $n_event = $this->toSignedNativeEvent($event, $sk);

        /**
         * @var array<array-key, RelayResponse> $response
         */
        return app(DummyWebSocket::class)->publish($n_event, $relays);
    }

    /**
     * @param  array<string>  $relays
     * @return array<array-key, Response>
     */
    public function list(Filter $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        $responses = app(DummyWebSocket::class)->request($filter, $relays);

        return collect($responses)
            ->map(function ($events, $relay) {
                return $this->response(['events' => collect($events)
                    ->filter(fn ($event) => $event instanceof RelayResponseEvent)
                    ->map(function ($event) {
                        return (array) $event->event;
                    })->toArray()]);
            })->toArray();
    }

    /**
     * @param  array<string>  $relays
     * @return array<array-key, Response>
     */
    public function get(Filter $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        $responses = app(DummyWebSocket::class)->request($filter, $relays);

        return collect($responses)
            ->map(function ($events, $relay) {
                return $this->response(['event' => collect($events)
                    ->filter(fn ($event) => $event instanceof RelayResponseEvent)
                    ->map(function ($event) {
                        return (array) $event->event;
                    })->first()]);
            })->toArray();
    }
}
