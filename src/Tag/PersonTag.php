<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tag;

use Illuminate\Contracts\Support\Arrayable;

class PersonTag implements Arrayable
{
    public function __construct(
        protected readonly string $pubkey,
        protected readonly string $relay = '',
        protected readonly string $petname = '',
    ) {
    }

    /**
     * @return array<string, string, string, string>
     */
    public function toArray(): array
    {
        return ['p', $this->pubkey, $this->relay, $this->petname];
    }
}
