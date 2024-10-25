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

    /**
     * Publish new Event.
     */
    public function publish(Event|array $event, #[\SensitiveParameter] string $sk, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        $n_event = $this->toSignedNativeEvent($event, $sk);

        $responses = app(DummyClient::class)->publish($n_event, $relay);

        $res = head($responses);

        if ($res instanceof RelayResponseNotice) {
            return $this->response(['message' => 'error', 'error' => $res->message()], 500);
        }

        if ($res instanceof RelayResponseOk && $res->isSuccess()) {
            return $this->response(['message' => 'ok', 'id' => $res->eventId]);
        }

        return $this->response(['message' => 'error', 'error' => 'error'], 500);
    }

    /**
     * Get event list.
     */
    public function list(Filter|array $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        $n_filter = $this->toNativeFilter($filter);
        $filters = [$n_filter];

        return app(DummyClient::class)->list($filters, $relay);
    }

    /**
     * Get first event.
     */
    public function get(Filter|array $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        $n_filter = $this->toNativeFilter($filter);
        $filters = [$n_filter];

        /** @var DummyClient $dummy */
        $dummy = app(DummyClient::class);
        return $dummy->get($filters, $relay);
    }

    public function hash(Event|array $event): Response
    {
        $n_event = $this->toNativeEvent($event);

        $signer = app(Sign::class);
        $hash = hash('sha256', $signer->serializeEvent($n_event));

        return $this->response([
            'hash' => $hash,
        ]);
    }

    public function sign(Event|array $event, #[\SensitiveParameter] string $sk): Response
    {
        $n_event = $this->toSignedNativeEvent($event, $sk);

        return $this->response([
            'sign' => $n_event->getSignature(),
        ]);
    }

    public function verify(Event|array $event): Response
    {
        $n_event = $this->toNativeEvent($event);

        return $this->response([
            'verify' => $n_event->verify(),
        ]);
    }
}
