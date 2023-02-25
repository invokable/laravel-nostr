<?php

namespace Revolution\Nostr\Nip19;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Nostr\Kind;

class ProfilePointer implements Arrayable
{
    public function __construct(
        protected readonly string $pubkey,
        protected readonly array $relays = [],
    ) {
    }

    public static function make(
        string $pubkey,
        array $relays = [],
    ): static {
        return new static(...func_get_args());
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
