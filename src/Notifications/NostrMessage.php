<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Contracts\Support\Arrayable;

class NostrMessage implements Arrayable
{
    public function __construct(
        public string $content,
        public array $tags = [],
    ) {
    }

    public static function create(string $content, array $tags = []): static
    {
        return new static(content: $content, tags: $tags);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
