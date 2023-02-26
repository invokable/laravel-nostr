<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;

/**
 * Working with multiple relays.
 */
class PendingPool
{
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

    /**
     * @param  array<string>  $relays
     * @return array<array-key, Response>
     */
    public function publish(Event|array $event, string $sk, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(
            fn (Pool $pool) => $this->publishRequests(
                pool: $pool,
                event: $event,
                sk: $sk,
                relays: $relays
            )
        );
    }

    private function publishRequests(Pool $pool, Event|array $event, string $sk, array $relays): array
    {
        return collect($relays)
            ->map(fn ($relay) => $pool->as($relay)
                                      ->baseUrl(Config::get('nostr.api_base'))
                                      ->post('event/publish', [
                                          'event' => collect($event)->toArray(),
                                          'sk' => $sk,
                                          'relay' => $relay,
                                      ]))
            ->toArray();
    }

    /**
     * @return array<array-key, Response>
     */
    public function list(array $filters, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(
            fn (Pool $pool) => $this->listRequests(
                pool: $pool,
                filters: $filters,
                relays: $relays
            )
        );
    }

    private function listRequests(Pool $pool, array $filters, array $relays): array
    {
        return collect($relays)
            ->map(fn ($relay) => $pool->as($relay)
                                      ->baseUrl(Config::get('nostr.api_base'))
                                      ->post('event/list', [
                                          'filter' => collect($filters)->toArray(),
                                          'relay' => $relay,
                                      ]))
            ->toArray();
    }

    /**
     * @return array<array-key, Response>
     */
    public function get(Filter|array $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(
            fn (Pool $pool) => $this->getRequests(
                pool: $pool,
                filter: $filter,
                relays: $relays
            )
        );
    }

    private function getRequests(Pool $pool, Filter|array $filter, array $relays): array
    {
        return collect($relays)
            ->map(fn ($relay) => $pool->as($relay)
                                      ->baseUrl(Config::get('nostr.api_base'))
                                      ->post('event/get', [
                                          'filter' => collect($filter)->toArray(),
                                          'relay' => $relay,
                                      ]))
            ->toArray();
    }
}
