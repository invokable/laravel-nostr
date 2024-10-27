<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasEvent;
use Revolution\Nostr\Client\Native\Concerns\HasFilter;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;
use swentel\nostr\Message\EventMessage;
use swentel\nostr\Message\RequestMessage;
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\RelayResponse\RelayResponseEose;
use swentel\nostr\RelayResponse\RelayResponseEvent;
use swentel\nostr\RelayResponse\RelayResponseOk;
use swentel\nostr\Subscription\Subscription;
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

    public function publish(Event $event, string $sk): array
    {
        $n_event = $this->toNativeEvent($event->sign($sk));

        if (! $n_event->verify()) {
            throw new \InvalidArgumentException();
        }

        $eventMessage = new EventMessage($n_event);

        $this->ws->write($eventMessage->generate());

        $start = now();

        do {
            $response = $this->ws->read();

            if (! empty($response)) {
                //dump($response);

                $event = rescue(fn () => RelayResponse::create(json_decode($response)));

                if ($event instanceof RelayResponseEose) {
                    break;
                }

                if ($event instanceof RelayResponseOk) {
                    break;
                }
            }
        } while (empty($response) || now()->addSeconds($this->timeout)->gt($start));

        return json_decode($response, true);
    }

    public function request(Filter $filter): array
    {
        $requestMessage = new RequestMessage(
            (new Subscription())->setId(),
            [$this->toNativeFilter($filter)],
        );

        $this->ws->write($requestMessage->generate());

        $start = now();

        $events = [];

        do {
            $response = $this->ws->read();

            if (! empty($response)) {
                //dump($response);

                $event = rescue(fn () => RelayResponse::create(json_decode($response)));

                if ($event instanceof RelayResponseEose) {
                    break;
                }

                if ($event instanceof RelayResponseEvent) {
                    $events[] = (array) $event->event;
                }
            }
        } while (empty($response) || now()->addSeconds($this->timeout)->gt($start));

        return $events;
    }

    public function list(Filter $filter): array
    {
        return $this->request($filter);
    }

    public function get(Filter $filter): array
    {
        $events = $this->request($filter);

        return head($events);
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
