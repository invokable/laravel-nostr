<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-34, 58, 72.
 */
class NameTag implements Arrayable
{
    public function __construct(
        protected readonly string $name,
    ) {}

    public static function make(string $name): static
    {
        return new static(name: $name);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['name', $this->name];
    }
}
