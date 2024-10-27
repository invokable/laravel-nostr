<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Revolution\Nostr\Client\Native\Concerns\HasEvent;
use Revolution\Nostr\Client\Native\Concerns\HasFilter;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Message\PublishEventMessage;
use Revolution\Nostr\Message\RequestEventMessage;
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\RelayResponse\RelayResponseEose;
use swentel\nostr\RelayResponse\RelayResponseEvent;
use swentel\nostr\RelayResponse\RelayResponseOk;
use Valtzu\WebSocketMiddleware\WebSocketStream;

class NativeWebSocket
{
    use HasEvent;
    use HasFilter;
    use HasHttp;
    use Macroable;
    use Conditionable;

    public function __construct(
        protected ?WebSocketStream $ws = null,
        protected int $timeout = 60,
    ) {
    }

    /**
     * Send EVENT message.
     */
    public function publish(Event $event, string $sk): array
    {
        if ($event->isUnsigned()) {
            $event->sign($sk);
        }

        if (! $event->validate() || ! $this->toNativeEvent($event)->verify()) {
            throw new InvalidArgumentException('Invalid event.');
        }

        $publish = new PublishEventMessage($event);

        //dump((string) $publish);

        $this->ws->write($publish->toJson());

        $timeout = now()->addSeconds($this->timeout);

        do {
            $response = $this->ws->read();

            if (! empty($response)) {
                //dump($response);

                $res = rescue(fn () => RelayResponse::create(json_decode($response)));

                if ($res instanceof RelayResponseEose) {
                    break;
                }

                if ($res instanceof RelayResponseOk) {
                    break;
                }
            }
        } while (now()->lte($timeout));

        return json_decode($response, true);
    }

    /**
     * Send REQ message.
     */
    public function request(Filter $filter): array
    {
        $req = new RequestEventMessage($filter);

        //dump($req->toJson());

        $this->ws->write($req->toJson());

        $timeout = now()->addSeconds($this->timeout);

        $events = [];

        do {
            try {
                $response = $this->ws->read();
            } catch (\Exception $e) {
                $events[] = ['NOTICE', $e->getMessage()];
            }

            if (! empty($response)) {
                //dump($response);

                $event = rescue(fn () => RelayResponse::create(json_decode($response)));

                if ($event instanceof RelayResponseEose) {
                    break;
                }

                if ($event instanceof RelayResponseEvent && $event->subscriptionId === $req->id) {
                    $events[] = (array) $event->event;
                }
            }
        } while (now()->lte($timeout));

        return $events;
    }

    public function list(Filter $filter): array
    {
        return $this->request($filter);
    }

    public function get(Filter $filter): array
    {
        $events = $this->request($filter);

        return Arr::first($events, default: []);
    }

    public function timeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getWebSocket(): WebSocketStream
    {
        return $this->ws;
    }

    public function setWebSocket(WebSocketStream $ws): self
    {
        $this->ws = $ws;

        return $this;
    }
}
