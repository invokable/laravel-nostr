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
    public function withRelays(array $relays): static;

    /**
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
