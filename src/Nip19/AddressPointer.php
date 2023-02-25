<?php

declare(strict_types=1);

namespace Revolution\Nostr\Nip19;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Nostr\Kind;

class AddressPointer implements Arrayable
{
    public function __construct(
        protected readonly string $identifier,
        protected readonly string $pubkey,
        protected readonly int|Kind $kind,
        protected readonly array $relays = [],
    ) {
    }

    public static function make(
        string $identifier,
        string $pubkey,
        int|Kind $kind,
        array $relays = [],
    ): static {
        return new static(...func_get_args());
    }

    public function toArray(): array
    {
        return collect(get_object_vars($this))
            ->map(fn ($item) => $item instanceof BackedEnum ? $item->value : $item)
            ->toArray();
    }
}
