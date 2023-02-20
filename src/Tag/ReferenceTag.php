<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tag;

use Illuminate\Contracts\Support\Arrayable;

class ReferenceTag implements Arrayable
{
    public function __construct(
        protected readonly string $r,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['r', $this->r];
    }
}
