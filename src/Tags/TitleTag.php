<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-23.
 */
class TitleTag implements Arrayable
{
    public function __construct(
        protected readonly string $title,
    ) {
    }

    public static function make(string $title): static
    {
        return new static(title: $title);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['title', $this->title];
    }
}
