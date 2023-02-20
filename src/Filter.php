<?php
declare(strict_types=1);

namespace Revolution\Nostr;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Stringable;

class Filter implements Jsonable, Arrayable, Stringable
{
    protected array $parameters = [];

    public function __construct(
        /** @var array<string>|null */
        public readonly ?array $ids = null,
        /** @var array<string>|null */
        public readonly ?array $authors = null,
        /** @var array<Kind|int>|null */
        public readonly ?array $kinds = null,
        public readonly ?int $since = null,
        public readonly ?int $until = null,
        public readonly ?int $limit = null,
    ) {
    }

    /**
     * Set extra parameters.
     * ->with(['#e' => ['...']])
     *
     * @param array<array-key, array<string>> $parameters
     */
    public function with(array $parameters,): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function toArray(): array
    {
        return collect(get_object_vars($this))
            ->except(['parameters'])
            ->merge($this->parameters)
            ->reject(fn ($item) => is_null($item))
            ->toArray();
    }

    public function toJson($options = 0,): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
