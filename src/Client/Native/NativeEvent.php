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

    public function publish(Event $event, #[\SensitiveParameter] string $sk, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        $response = Http::ws($relay, fn (NativeWebSocket $ws) => $this->response($ws->publish($event, $sk)));

        return $this->publishResponse($response);
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
