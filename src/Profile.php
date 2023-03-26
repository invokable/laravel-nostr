<?php

declare(strict_types=1);

namespace Revolution\Nostr;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JetBrains\PhpStorm\ArrayShape;
use Stringable;

class Profile implements Jsonable, Arrayable, Stringable
{
    public function __construct(
        public string $name = '',
        public string $display_name = '',
        public string $about = '',
        public string $picture = '',
        public string $banner = '',
        public string $website = '',
        public string $nip05 = '',
        public string $lud06 = '',
        public string $lud16 = '',
    ) {
    }

    /**
     * @param  array{
     *     name?: string,
     *     display_name?: string,
     *     about?: string,
     *     picture?: string,
     *     banner?: string,
     *     website?: string,
     *     nip05?: string,
     *     lud06?: string,
     *     lud16?: string,
     * }  $profile
     */
    public static function fromArray(array $profile): static
    {
        return new static(...$profile);
    }

    public static function fromJson(string $profile): static
    {
        return static::fromArray(json_decode($profile, true));
    }

    #[ArrayShape([
        'name' => 'string',
        'display_name' => 'string',
        'about' => 'string',
        'picture' => 'string',
        'banner' => 'string',
        'website' => 'string',
        'nip05' => 'string',
        'lud06' => 'string',
        'lud16' => 'string',
    ])]
    public function toArray(): array
    {
        return get_object_vars($this);
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
