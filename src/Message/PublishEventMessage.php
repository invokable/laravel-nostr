<?php

namespace Revolution\Nostr\Message;

use Illuminate\Contracts\Support\Jsonable;
use InvalidArgumentException;
use Revolution\Nostr\Event;
use Stringable;

final class PublishEventMessage implements Jsonable, Stringable
{
    protected const TYPE = 'EVENT';

    public function __construct(
        protected readonly Event $event,
    ) {}

    public static function make(Event $event): self
    {
        return new self($event);
    }

    public function toJson($options = 0): string
    {
        if ($this->event->isUnsigned()) {
            throw new InvalidArgumentException('Event message is not signed');
        }

        if ($options === 0) {
            $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        return collect([self::TYPE, $this->event->toArray()])->toJson($options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
