<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Contracts\Support\Arrayable;

class NostrMessage implements Arrayable
{
    public function __construct(
        public readonly string $content,
        public readonly int $kind = 1,
        public readonly array $tags = [],
    ) {
    }

    public static function create(string $content, int $kind = 1, array $tags = []): static
    {
        return new static(content: $content, kind: $kind, tags: $tags);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
