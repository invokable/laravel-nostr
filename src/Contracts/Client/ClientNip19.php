<?php

namespace Revolution\Nostr\Contracts\Client;

use Illuminate\Http\Client\Response;
use Revolution\Nostr\Nip19\AddressPointer;
use Revolution\Nostr\Nip19\EventPointer;
use Revolution\Nostr\Nip19\ProfilePointer;

interface ClientNip19
{
    /**
     * Decode NIP-19 string.
     *
     * @param  string  $n  nsec, npub, note, nprofile, nevent, naddr
     */
    public function decode(string $n): Response;

    /**
     * Encode note id.
     */
    public function note(string $id): Response;

    /**
     * Encode profile.
     */
    public function nprofile(ProfilePointer $profile): Response;

    /**
     * Encode event.
     */
    public function nevent(EventPointer $event): Response;

    /**
     * Encode addr.
     */
    public function naddr(AddressPointer $addr): Response;
}
