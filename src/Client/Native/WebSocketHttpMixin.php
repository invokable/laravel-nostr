<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Psr\Http\Message\MessageInterface;
use RuntimeException;
use Valtzu\WebSocketMiddleware\WebSocketMiddleware;
use Valtzu\WebSocketMiddleware\WebSocketStream;

class WebSocketHttpMixin
{
    /**
     * Http::ws(string $url, callable $callback): mixed.
     */
    public function ws(): callable
    {
        return function (string $url, callable $callback): mixed {
            /** @var PendingRequest $this */
            if (filled($this->stubCallbacks)) {
                return $this->send('GET', $url);
            }

            $pending = $this->withMiddleware(new WebSocketMiddleware)
                ->sendRequest('GET', $url);

            $websocket = function (MessageInterface|Psr7Response $response) use ($callback) {
                if ($response->getStatusCode() === 101) {
                    /** @var WebSocketStream $ws */
                    $ws = $response->getBody();

                    if ($ws instanceof WebSocketStream) {
                        return $callback(new NativeWebSocket($ws), new Response($response));
                    }
                }

                throw new RuntimeException('WebSocket connection failed.');
            };

            return match (true) {
                $pending instanceof MessageInterface => $websocket($pending),
                $pending instanceof PromiseInterface => $this->promise = $pending
                    ->then(fn (Psr7Response $response) => $websocket($response)),
                default => throw new RuntimeException('WebSocket connection failed.'),
            };
        };
    }
}
