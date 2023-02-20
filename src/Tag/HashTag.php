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

    public function toArray(): array
    {
        return ['t', $this->hashtag];
    }
}
