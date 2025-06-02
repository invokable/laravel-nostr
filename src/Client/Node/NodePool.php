<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Node;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Contracts\Client\ClientPool;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;

/**
 * Working with multiple relays.
 */
class NodePool implements ClientPool
{
    use Conditionable;
    use Macroable;

    protected array $relays = [];

    public function __construct()
    {
        $this->relays = Config::get('nostr.relays') ?? [];
    }

    public function withRelays(array $relays): static
    {
        $this->relays = $relays;

        return $this;
    }

    public function publish(Event $event, #[\SensitiveParameter] string $sk, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(
            fn (Pool $pool) => $this->publishRequests(
                pool: $pool,
                event: $event,
                sk: $sk,
                relays: $relays,
            ),
        );
    }

    private function publishRequests(Pool $pool, Event $event, #[\SensitiveParameter] string $sk, array $relays): array
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

    public function list(Filter $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(
            fn (Pool $pool) => $this->listRequests(
                pool: $pool,
                filter: $filter,
                relays: $relays,
            ),
        );
    }

    private function listRequests(Pool $pool, Filter $filter, array $relays): array
    {
        return collect($relays)
            ->map(fn ($relay) => $pool->as($relay)
                ->baseUrl(Config::get('nostr.api_base'))
                ->post('event/list', [
                    'filter' => collect($filter)->toArray(),
                    'relay' => $relay,
                ]))
            ->toArray();
    }

    public function get(Filter $filter, array $relays = []): array
    {
        $relays = blank($relays) ? $this->relays : $relays;

        return Http::pool(
            fn (Pool $pool) => $this->getRequests(
                pool: $pool,
                filter: $filter,
                relays: $relays,
            ),
        );
    }

    private function getRequests(Pool $pool, Filter $filter, array $relays): array
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
