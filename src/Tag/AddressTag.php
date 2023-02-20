<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tag;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Nostr\Kind;

class AddressTag implements Arrayable
{
    public function __construct(
        protected readonly int $kind,
        protected readonly string $pubkey,
        protected readonly string $identifier,
        protected readonly string $relay = '',
    ) {
    }

    /**
     * @return array<string, string, string>
     */
    public function toArray(): array
    {
        $addr = collect([
            $this->kind,
            $this->pubkey,
            $this->identifier,
        ])->join('|');

        return ['a', $addr, $this->relay];
    }
}
