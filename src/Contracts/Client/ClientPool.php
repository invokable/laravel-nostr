<?php

namespace Revolution\Nostr\Contracts\Client;

use Illuminate\Http\Client\Response;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;

/**
 * Working with multiple relays.
 */
interface ClientPool
{
    /**
     * @param  array<string>  $relays
     */
    public function withRelays(array $relays): static;

    /**
     * @param  Event  $event  Unsigned Event
     * @param  string  $sk  Secret key
     * @param  array<string>  $relays
     * @return array<array-key, Response>
     */
    public function publish(Event $event, string $sk, array $relays = []): array;

    /**
     * @param  array<string>  $relays
     * @return array<array-key, Response>
     */
    public function list(Filter $filter, array $relays = []): array;

    /**
     * @param  array<string>  $relays
     * @return array<array-key, Response>
     */
    public function get(Filter $filter, array $relays = []): array;
}
