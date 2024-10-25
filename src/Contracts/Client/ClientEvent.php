<?php

namespace Revolution\Nostr\Contracts\Client;

use Illuminate\Http\Client\Response;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;

interface ClientEvent
{
    public function withRelay(string $relay): static;

    /**
     * Publish new Event.
     */
    public function publish(Event|array $event, string $sk, ?string $relay = null): Response;

    /**
     * Get event list.
     */
    public function list(Filter|array $filter, ?string $relay = null): Response;

    /**
     * Get first event.
     */
    public function get(Filter|array $filter, ?string $relay = null): Response;

    public function hash(Event|array $event): Response;

    public function sign(Event|array $event, string $sk): Response;

    public function verify(Event|array $event): Response;
}
