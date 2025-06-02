<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

class ImageTag implements Arrayable
{
    public function __construct(
        protected readonly string $url,
        protected readonly int|string|null $width = null,
        protected readonly int|string|null $height = null,
    ) {}

    public static function make(string $url, int|string|null $width = null, int|string|null $height = null): static
    {
        return new static(...func_get_args());
    }

    /**
     * @return array{0: string, 1: string, ?2: string}
     */
    public function toArray(): array
    {
        if (filled($this->width) && filled($this->height)) {
            return ['image', $this->url, $this->width.'x'.$this->height];
        }

        return ['image', $this->url];
    }
}
