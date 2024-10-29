<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Nostr\Kind;

use function Illuminate\Support\enum_value;

class AddressTag implements Arrayable
{
    public function __construct(
        protected readonly int|Kind $kind,
        protected readonly string $pubkey,
        protected readonly string $identifier,
        protected readonly string $relay = '',
    ) {
    }

    public static function make(
        int|Kind $kind,
        string $pubkey,
        string $identifier,
        string $relay = '',
    ): static {
        return new static(...func_get_args());
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public function toArray(): array
    {
        $addr = collect([
            enum_value($this->kind),
            $this->pubkey,
            $this->identifier,
        ])->join(':');

        return ['a', $addr, $this->relay];
    }
}
