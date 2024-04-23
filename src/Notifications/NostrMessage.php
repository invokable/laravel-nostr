<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Nostr\Kind;

class NostrMessage implements Arrayable
{
    public function __construct(
        public readonly string $content,
        public readonly int|Kind $kind = Kind::Text,
        public readonly array $tags = [],
    ) {
        //
    }

    public static function create(string $content, int|Kind $kind = Kind::Text, array $tags = []): static
    {
        return new static(...func_get_args());
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
