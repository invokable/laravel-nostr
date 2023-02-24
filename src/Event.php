<?php

declare(strict_types=1);

namespace Revolution\Nostr;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Stringable;

class Event implements Jsonable, Arrayable, Stringable
{
    protected readonly string $id;
    protected readonly string $pubkey;
    protected readonly string $sig;

    public function __construct(
        protected readonly int|Kind $kind = Kind::Metadata,
        protected readonly string $content = '',
        protected readonly int $created_at = 0,
        protected readonly array $tags = [],
    ) {
    }

    /**
     * Make new event.
     */
    public static function make(
        int|Kind $kind = Kind::Metadata,
        string $content = '',
        int $created_at = 0,
        array $tags = [],
    ): static {
        return new static(...func_get_args());
    }

    /**
     * From signed event.
     */
    public static function makeSigned(
        int|Kind $kind,
        string $content,
        int $created_at,
        array $tags,
        string $id,
        string $pubkey,
        string $sig,
    ): static {
        return (new static(kind: $kind, content: $content, created_at: $created_at,
            tags: $tags))
            ->withId(id: $id)
            ->withPublicKey(pubkey: $pubkey)
            ->withSign(sig: $sig);
    }

    public function withId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function withPublicKey(string $pubkey): static
    {
        $this->pubkey = $pubkey;

        return $this;
    }

    public function withSign(string $sig): static
    {
        $this->sig = $sig;

        return $this;
    }

    public function toArray(): array
    {
        return collect(get_object_vars($this))
            ->reject(fn ($item) => is_null($item))
            ->map(fn ($item) => $item instanceof BackedEnum ? $item->value : $item)
            ->map($this->castTags(...))
            ->toArray();
    }

    protected function castTags(mixed $item, string $key): mixed
    {
        if ($key === 'tags' && is_array($item)) {
            $item = collect($item)
                ->map(fn ($tag) => $tag instanceof Arrayable ? $tag->toArray() : $tag)
                ->toArray();
        }

        return $item;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
