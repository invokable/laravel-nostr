<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Node;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Node\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientEvent;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;

/**
 * Working with single relay.
 */
class NodeEvent implements ClientEvent
{
    use HasHttp;
    use Macroable;
    use Conditionable;

    protected string $relay = '';

    public function __construct()
    {
        $this->relay = Config::get('nostr.relays.0', '');
    }

    public function withRelay(string $relay): static
    {
        $this->relay = $relay;

        return $this;
    }

    public function publish(Event $event, #[\SensitiveParameter] string $sk, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        return $this->http()->post('event/publish', [
            'event' => collect($event)->toArray(),
            'sk' => $sk,
            'relay' => $relay,
        ]);
    }

    public function list(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        return $this->http()->post('event/list', [
            'filter' => collect($filter)->toArray(),
            'relay' => $relay,
        ]);
    }

    public function get(Filter $filter, ?string $relay = null): Response
    {
        $relay = $relay ?? $this->relay;

        return $this->http()->post('event/get', [
            'filter' => collect($filter)->toArray(),
            'relay' => $relay,
        ]);
    }

    public function hash(Event $event): Response
    {
        return $this->http()->post('event/hash', [
            'event' => collect($event)->toArray(),
        ]);
    }

    public function sign(Event $event, #[\SensitiveParameter] string $sk): Response
    {
        return $this->http()->post('event/sign', [
            'event' => collect($event)->toArray(),
            'sk' => $sk,
        ]);
    }

    public function verify(Event $event): Response
    {
        return $this->http()->post('event/verify', [
            'event' => collect($event)->toArray(),
        ]);
    }
}
