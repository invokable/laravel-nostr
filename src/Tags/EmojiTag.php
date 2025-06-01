<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

class EmojiTag implements Arrayable
{
    public function __construct(
        protected readonly string $shortcode,
        protected readonly string $url,
    ) {}

    public static function make(string $shortcode, string $url): static
    {
        return new static(shortcode: $shortcode, url: $url);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public function toArray(): array
    {
        return ['emoji', $this->shortcode, $this->url];
    }
}
