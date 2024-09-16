<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Nostr\Kind;

final readonly class NostrMessage implements Arrayable
{
    public function __construct(
        public string $content,
        public int|Kind $kind = Kind::Text,
        public array $tags = [],
    ) {
        //
    }

    public static function create(string $content, int|Kind $kind = Kind::Text, array $tags = []): self
    {
        return new self(...func_get_args());
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
