<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tag;

use Illuminate\Contracts\Support\Arrayable;

class HashTag implements Arrayable
{
    public function __construct(
        protected readonly string $hashtag,
    ) {
    }

    public static function make(string $hashtag,): static
    {
        return new static(hashtag: $hashtag);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['t', $this->hashtag];
    }
}
