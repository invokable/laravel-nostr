<?php

namespace Revolution\Nostr\Message;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use Revolution\Nostr\Filter;
use Stringable;

class RequestEventMessage implements Stringable, Jsonable
{
    protected const TYPE = 'REQ';

    protected array $filters = [];

    public readonly string $id;

    public function __construct(Filter $filter)
    {
        $this->filters[] = $filter->toArray();
        $this->id = Str::random(64);
    }

    public static function make(Filter $filter): self
    {
        return new self($filter);
    }

    public function addFilter(Filter $filter): self
    {
        $this->filters[] = $filter->toArray();

        return $this;
    }

    public function toJson($options = 0): string
    {
        if ($options === 0) {
            $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        return collect([self::TYPE, $this->id])->merge($this->filters)->toJson($options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
