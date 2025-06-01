<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

class DescriptionTag implements Arrayable
{
    public function __construct(
        protected readonly string $description,
    ) {}

    public static function make(string $description): static
    {
        return new static(description: $description);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['description', $this->description];
    }
}
