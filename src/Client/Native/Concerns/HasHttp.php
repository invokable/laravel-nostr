<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native\Concerns;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait HasHttp
{
    /**
     * Convert to Laravel Http Response for compatibility with node driver.
     */
    protected function response(array|string|null $body = null, int $status = 200, array $headers = []): Response
    {
        return new Response(Http::response($body, $status, $headers)->wait());
    }
}
