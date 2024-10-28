<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native\Concerns;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\RelayResponse\RelayResponseNotice;
use swentel\nostr\RelayResponse\RelayResponseOk;

trait HasHttp
{
    /**
     * Convert to Laravel Http Response for compatibility with node driver.
     */
    protected function response(array|string|null $body = null, int $status = 200, array $headers = []): Response
    {
        return new Response(Http::response($body, $status, $headers)->wait());
    }

    /**
     * Convert the publish response.
     */
    protected function publishResponse(Response $response): Response
    {
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
    }
}
