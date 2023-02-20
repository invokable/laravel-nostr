<?php
declare(strict_types=1);

namespace Revolution\Nostr\Client\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

trait HasHttp
{
    protected function http(): PendingRequest
    {
        return Http::baseUrl(Config::get('nostr.api_base'));
    }
}
