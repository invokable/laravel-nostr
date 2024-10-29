<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Nostr\Kind;

use function Illuminate\Support\enum_value;

class KindTag implements Arrayable
{
    public function __construct(
        protected readonly int|Kind $kind,
    ) {
    }

    public static function make(int|Kind $kind): static
    {
        return new static(kind: $kind);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['k', (string) enum_value($this->kind)];
    }
}
