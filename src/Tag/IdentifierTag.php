<?php
declare(strict_types=1);

namespace Revolution\Nostr\Tag;

use Illuminate\Contracts\Support\Arrayable;

class IdentifierTag implements Arrayable
{
    public function __construct(
        protected readonly string $d,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['d', $this->d];
    }
}
