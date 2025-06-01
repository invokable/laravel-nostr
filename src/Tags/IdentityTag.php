<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-39.
 */
class IdentityTag implements Arrayable
{
    protected array $parameters = [];

    public function __construct(
        protected readonly string $username,
        protected readonly string $proof,
    ) {}

    public static function make(string $username, string $proof): static
    {
        return new static(username: $username, proof: $proof);
    }

    public function with(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return array_merge(['i', $this->username, $this->proof], $this->parameters);
    }
}
