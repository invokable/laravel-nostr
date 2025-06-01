<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-14.
 */
class SubjectTag implements Arrayable
{
    public function __construct(
        protected readonly string $subject,
    ) {}

    public static function make(string $subject): static
    {
        return new static(subject: $subject);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['subject', $this->subject];
    }
}
