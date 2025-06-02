<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

class RelayTag implements Arrayable
{
    public function __construct(
        protected readonly string $relay,
        protected readonly string $maker = '',
    ) {}

    public static function make(string $relay, string $maker = ''): static
    {
        return new static(relay: $relay, maker: $maker);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public function toArray(): array
    {
        return ['relay', $this->relay, $this->maker];
    }
}
