<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasEvent;
use Revolution\Nostr\Client\Native\Concerns\HasFilter;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientEvent;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\RelayResponse\RelayResponseNotice;
use swentel\nostr\RelayResponse\RelayResponseOk;

/**
 * Working with single relay.
 */
class NativeEvent implements ClientEvent
{
    use HasHttp;
    use HasEvent;
    use HasFilter;
    use Macroable;

    protected string $relay = '';

    public function __construct()
    {
        $this->relay = Config::get('nostr.relays.0', '');
    }

    public function withRelay(string $relay): static
    {
        $this->relay = $relay;

        return $this;
    }

    public function publish(Event $event, #[\SensitiveParameter] string $sk, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        $response = Http::ws($relay, fn (NativeWebSocket $ws) => $this->response($ws->publish($event, $sk)));

        $res = RelayResponse::create($response->json());

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
    }

    public function list(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        return Http::ws($relay, fn (NativeWebSocket $ws) => $this->response(['events' => $ws->list($filter)]));
    }

    public function get(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        return Http::ws($relay, fn (NativeWebSocket $ws) => $this->response(['event' => $ws->get($filter)]));
    }

    public function hash(Event $event): Response
    {
        return $this->response([
            'hash' => $event->hash(),
        ]);
    }

    public function sign(Event $event, #[\SensitiveParameter] string $sk): Response
    {
        $signed_event = $event->sign($sk);

        return $this->response([
            'sign' => $signed_event->sig,
        ]);
    }

    public function verify(Event $event): Response
    {
        $n_event = $this->toNativeEvent($event);

        return $this->response([
            'verify' => $n_event->verify(),
        ]);
    }
}
