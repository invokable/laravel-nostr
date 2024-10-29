<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

class EventTag implements Arrayable
{
    public function __construct(
        protected readonly string $id,
        protected readonly string $relay = '',
        protected readonly string $marker = '',
    ) {
    }

    public static function make(
        string $id,
        string $relay = '',
        string $marker = '',
    ): static {
        return new static(...func_get_args());
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    public function toArray(): array
    {
        return ['e', $this->id, $this->relay, $this->marker];
    }
}
