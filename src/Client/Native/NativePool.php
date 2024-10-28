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
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\RelayResponse\RelayResponseNotice;
use swentel\nostr\RelayResponse\RelayResponseOk;

/**
 * Working with multiple relays.
 */
class NativePool implements ClientPool
{
    use HasHttp;
    use HasEvent;
    use Macroable;
    use Conditionable;

    protected array $relays = [];

    public function __construct()
    {
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
            ->map(function ($response) {
                $res = RelayResponse::create($response->json() ?? ['NOTICE', 'error']);

                if ($res instanceof RelayResponseNotice) {
                    return $this->response([
                        'message' => 'error',
                        'type' => $res->type,
                        'error' => $res->message(),
                    ], 500);
                }

                if ($res instanceof RelayResponseOk && $res->isSuccess()) {
                    return $this->response([
                        'message' => $res->type,
                        'id' => $res->eventId,
                    ]);
                }

                return $this->response(['message' => 'error', 'error' => 'error'], 500);
            })->toArray();
    }

    /**
     * @param  array<string>  $relays
     * @return array<array-key, Response>
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
     * @param  array<string>  $relays
     * @return array<array-key, Response>
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
