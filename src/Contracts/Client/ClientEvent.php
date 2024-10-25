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
    public function publish(Event $event, string $sk, ?string $relay = null): Response;

    /**
     * Get event list.
     */
    public function list(Filter $filter, ?string $relay = null): Response;

    /**
     * Get first event.
     */
    public function get(Filter $filter, ?string $relay = null): Response;

    public function hash(Event $event): Response;

    public function sign(Event $event, string $sk): Response;

    public function verify(Event $event): Response;
}
