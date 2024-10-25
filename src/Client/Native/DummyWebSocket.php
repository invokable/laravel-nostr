<?php

namespace Revolution\Nostr\Client\Native;

use Closure;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Revolution\Nostr\Client\Native\Concerns\HasFilter;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Filter;
use swentel\nostr\Event\Event;
use swentel\nostr\Message\EventMessage;
use swentel\nostr\Message\RequestMessage;
use swentel\nostr\Relay\RelaySet;
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\RelayResponse\RelayResponseEvent;
use swentel\nostr\RelayResponse\RelayResponseNotice;
use swentel\nostr\Request\Request;
use swentel\nostr\Subscription\Subscription;

/**
 * @todo
 */
class DummyWebSocket
{
    use HasHttp;
    use HasFilter;

    protected static ?Closure $fake = null;

    /**
     * Send EVENT message.
     */
    public function publish(Event $event, array|string $relay): array
    {
        if (static::$fake) {
            return call_user_func(static::$fake);
        }

        if (! $event->verify()) {
            throw new \InvalidArgumentException();
        }

        $relay_set = new RelaySet();
        $relay_set->createFromUrls(Arr::wrap($relay));
        $eventMessage = new EventMessage($event);
        $relay_set->setMessage($eventMessage);

        try {
            /** @var array<array-key, RelayResponse> $response */
            $responses = $relay_set->send();
        } catch (Exception $exception) {
            $responses = ['error' => RelayResponseNotice::create([
                'ERROR',
                $exception->getMessage(),
            ])];
        }

        return $responses;
    }

    /**
     * Send REQ message.
     */
    public function request(Filter $filter, array|string $relay): array
    {
        if (static::$fake) {
            return call_user_func(static::$fake);
        }

        $subscription = new Subscription();
        $subscriptionId = $subscription->setId();

        $filters = [$this->toNativeFilter($filter)];

        $requestMessage = new RequestMessage($subscriptionId, $filters);

        $relay_set = new RelaySet();
        $relay_set->createFromUrls(Arr::wrap($relay));
        $request = new Request($relay_set, $requestMessage);

        return $request->send();
    }

    public function list(Filter $filter, array|string $relay): Response
    {
        if (static::$fake) {
            return call_user_func(static::$fake);
        }

        try {
            $responses = $this->request($filter, $relay);
        } catch (Exception $exception) {
            return $this->response(['message' => 'error', 'error' => $exception->getMessage()], 500);
        }

        $events = collect($responses[$relay] ?? [])
            ->filter(fn ($response) => $response instanceof RelayResponseEvent)
            ->map(function ($event) {
                return (array) $event->event;
            })->toArray();

        return $this->response(['events' => $events]);
    }

    public function get(Filter $filter, array|string $relay): Response
    {
        if (static::$fake) {
            return call_user_func(static::$fake);
        }

        try {
            $responses = $this->request($filter, $relay);
        } catch (Exception $exception) {
            return $this->response(['message' => 'error', 'error' => $exception->getMessage()], 500);
        }

        /** @var RelayResponse $res */
        $res = collect($responses[$relay] ?? [])
            ->first(fn ($response) => $response instanceof RelayResponse);

        if ($res instanceof RelayResponseEvent) {
            $event = (array) $res->event;

            if ($res->isSuccess() && filled($event)) {
                return $this->response(['event' => $event]);
            }
        }

        if ($res instanceof RelayResponseNotice) {
            return $this->response(['message' => 'error', 'error' => $res->message()], 500);
        }

        if (data_get($responses, $relay.'.0.0') === 'ERROR') {
            $error_message = data_get($responses, $relay.'.0.3', 'error');

            return $this->response(['message' => 'error', 'error' => $error_message], 500);
        }

        return $this->response(['message' => 'error', 'error' => 'error'], 500);
    }

    public static function fake(?callable $callback = null): void
    {
        static::$fake = $callback;
    }
}
