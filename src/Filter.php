<?php

declare(strict_types=1);

namespace Revolution\Nostr;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Stringable;

class Filter implements Jsonable, Arrayable, Stringable
{
    protected array $parameters = [];

    /**
     * @param  array<string>|null  $ids
     * @param  array<string>|null  $authors
     * @param  array<int|Kind>|null  $kinds
     */
    public function __construct(
        public readonly ?array $ids = null,
        public readonly ?array $authors = null,
        public readonly ?array $kinds = null,
        public readonly ?int $since = null,
        public readonly ?int $until = null,
        public readonly ?int $limit = null,
        public readonly ?string $search = null,
    ) {
    }

    public static function make(
        ?array $ids = null,
        ?array $authors = null,
        ?array $kinds = null,
        ?int $since = null,
        ?int $until = null,
        ?int $limit = null,
        ?string $search = null,
    ): static {
        return new static(...func_get_args());
    }

    /**
     * Set extra parameters.
     *
     * Example:
     * <code>
     * $filter->with(['#e' => ['...', '...'], '#r' => [...]]);
     * </code>
     *
     * @param  array<string, array<string>>  $parameters
     */
    public function with(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array{
     *     ids?: array<string>,
     *     authors?: array<string>,
     *     kinds?: array<int>,
     *     since?: int,
     *     until?: int,
     *     limit?: int,
     *     search?: string,
     * }
     */
    public function toArray(): array
    {
        return collect(get_object_vars($this))
            ->except(['parameters'])
            ->merge($this->parameters)
            ->reject(fn ($item) => is_null($item))
            ->map($this->castEnum(...))
            ->toArray();
    }

    /**
     * Convert an array containing BackedEnum.
     */
    protected function castEnum(mixed $item): mixed
    {
        if (is_array($item)) {
            $item = collect($item)
                ->map(fn ($item) => $item instanceof BackedEnum ? $item->value : $item)
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
