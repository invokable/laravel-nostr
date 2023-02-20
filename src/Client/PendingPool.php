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
     * @return array<Response>
     */
    public function publish(Event|array $event, string $sk, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(fn (Pool $pool) => collect($relays)->map(fn ($relay,
        ) => $pool->baseUrl(Config::get('nostr.api_base'))
                  ->post('event/publish', [
                      'event' => collect($event)->toArray(),
                      'sk' => $sk,
                      'relay' => $relay,
                  ]))->toArray());
    }

    /**
     * @return array<Response>
     */
    public function list(array $filters, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(fn (Pool $pool) => collect($relays)->map(fn ($relay,
        ) => $pool->baseUrl(Config::get('nostr.api_base'))
                  ->post('event/list', [
                      'filters' => collect($filters)->toArray(),
                      'relay' => $relay,
                  ]))->toArray());
    }

    /**
     * @return array<Response>
     */
    public function get(Filter|array $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(fn (Pool $pool) => collect($relays)->map(fn ($relay,
        ) => $pool->baseUrl(Config::get('nostr.api_base'))
                  ->post('event/get', [
                      'filter' => collect($filter)->toArray(),
                      'relay' => $relay,
                  ]))->toArray());
    }
}
