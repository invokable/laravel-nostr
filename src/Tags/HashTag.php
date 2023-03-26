<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-12.
 */
class HashTag implements Arrayable
{
    /**
     * @param  string  $t  hashtag
     */
    public function __construct(
        protected readonly string $t,
    ) {
    }

    /**
     * @param  string  $t  hashtag
     */
    public static function make(string $t): static
    {
        return new static(t: $t);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['t', $this->t];
    }
}
