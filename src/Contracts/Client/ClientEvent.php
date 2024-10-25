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
     *
     * @param  Event  $event  Unsigned Event
     * @param  string  $sk  Secret key
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

    /**
     * Get the event hash, used as event id.
     *
     * @param  Event  $event  Unsigned Event
     */
    public function hash(Event $event): Response;

    /**
     * Get the event sig.
     *
     * @param  Event  $event  Unsigned Event
     */
    public function sign(Event $event, string $sk): Response;

    /**
     * @param  Event  $event  Signed Event
     */
    public function verify(Event $event): Response;
}
