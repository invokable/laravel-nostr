<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasEvent;
use Revolution\Nostr\Client\Native\Concerns\HasFilter;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientEvent;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;
use swentel\nostr\RelayResponse\RelayResponseNotice;
use swentel\nostr\RelayResponse\RelayResponseOk;
use swentel\nostr\Sign\Sign;

/**
 * Working with single relay.
 */
class NativeEvent implements ClientEvent
{
    use HasHttp;
    use HasEvent;
    use HasFilter;
    use Macroable;

    public function __construct(
        protected string $relay = '',
    ) {
        $this->relay = Arr::first(Config::get('nostr.relays', []));
    }

    public function withRelay(string $relay): static
    {
        $this->relay = $relay;

        return $this;
    }

    public function publish(Event $event, #[\SensitiveParameter] string $sk, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        $signed_event = $this->toSignedNativeEvent($event, $sk);

        $responses = app(DummyWebSocket::class)->publish($signed_event, $relay);

        $res = head($responses);

        if ($res instanceof RelayResponseNotice) {
            return $this->response([
                'message' => 'error',
                'type' => $res->type,
                'error' => $res->message(),
            ], 500);
        }

        if ($res instanceof RelayResponseOk && $res->isSuccess()) {
            return $this->response([
                'message' => 'ok',
                'id' => $res->eventId,
            ]);
        }

        return $this->response(['message' => 'error', 'error' => 'error'], 500);
    }

    public function list(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        /** @var DummyWebSocket $ws */
        $ws = app(DummyWebSocket::class);

        return $ws->list($filter, $relay);
    }

    public function get(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        /** @var DummyWebSocket $ws */
        $ws = app(DummyWebSocket::class);

        return $ws->get($filter, $relay);
    }

    public function hash(Event $event): Response
    {
        $n_event = $this->toNativeEvent($event);

        $signer = app(Sign::class);
        $hash = hash('sha256', $signer->serializeEvent($n_event));

        return $this->response([
            'hash' => $hash,
        ]);
    }

    public function sign(Event $event, #[\SensitiveParameter] string $sk): Response
    {
        $signed_event = $this->toSignedNativeEvent($event, $sk);

        return $this->response([
            'sign' => $signed_event->getSignature(),
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
