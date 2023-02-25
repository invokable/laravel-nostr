<?php

declare(strict_types=1);

namespace Revolution\Nostr\Nip19;

use Illuminate\Contracts\Support\Arrayable;

class EventPointer implements Arrayable
{
    public function __construct(
        protected readonly string $id,
        protected readonly array $relays = [],
    ) {
    }

    public static function make(
        string $id,
        array $relays = [],
    ): static {
        return new static(...func_get_args());
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
