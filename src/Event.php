<?php

declare(strict_types=1);

namespace Revolution\Nostr;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Mdanter\Ecc\Crypto\Signature\SchnorrSignature;
use Stringable;
use swentel\nostr\Key\Key;
use Throwable;

final class Event implements Arrayable, Jsonable, Stringable
{
    use Conditionable;
    use Macroable;
    use Tappable;

    public readonly string $id;

    public readonly string $pubkey;

    public readonly string $sig;

    public function __construct(
        public readonly int|Kind $kind = Kind::Metadata,
        public readonly string $content = '',
        public int $created_at = 0,
        public readonly array $tags = [],
    ) {
        if ($this->created_at === 0) {
            $this->created_at = now()->timestamp;
        }
    }

    /**
     * Make new event.
     */
    public static function make(
        int|Kind $kind = Kind::Metadata,
        string $content = '',
        int $created_at = 0,
        array $tags = [],
    ): self {
        return new self(...func_get_args());
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
    ): self {
        return self::make(
            kind: $kind,
            content: $content,
            created_at: $created_at,
            tags: $tags,
        )->withId(id: $id)->withPublicKey(pubkey: $pubkey)->withSign(sig: $sig);
    }

    /**
     * Make signed event from array.
     *
     * @param  array{
     *     kind: int|Kind,
     *     content: string,
     *     created_at: int,
     *     tags: array,
     *     id: int,
     *     pubkey: string,
     *     sig: string
     * }  $event
     */
    public static function fromArray(array $event): self
    {
        return self::makeSigned(...$event);
    }

    public function validate(): bool
    {
        return Validator::make(data: $this->toArray(), rules: [
            'kind' => 'required|filled|numeric|integer',
            'content' => 'string',
            'created_at' => 'required|filled|numeric|integer',
            'tags' => 'array',
            'id' => 'sometimes|required|filled|string|size:64',
            'pubkey' => 'sometimes|required|filled|string|size:64',
            'sig' => 'sometimes|required|filled|string|size:128',
        ])->passes();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withPublicKey(string $pubkey): self
    {
        $this->pubkey = $pubkey;

        return $this;
    }

    public function withSign(string $sig): self
    {
        $this->sig = $sig;

        return $this;
    }

    public function isUnsigned(): bool
    {
        return ! $this->isSigned();
    }

    public function isSigned(): bool
    {
        return isset($this->sig);
    }

    public function sign(string $sk): self
    {
        return $this->unless(
            isset($this->pubkey),
            fn () => $this->withPublicKey(str_pad((new Key)->getPublicKey($sk), 64, '0', STR_PAD_LEFT)),
        )->unless(
            isset($this->id),
            fn () => $this->withId($this->hash()),
        )->unless(
            isset($this->sig),
            function () use ($sk) {
                $signatureData = (new SchnorrSignature)->sign($sk, $this->id);
                $rawSignature = data_get($signatureData, 'signature', '');
                
                // Ensure signature is exactly 128 characters by padding each 64-character component
                if (strlen($rawSignature) < 128) {
                    // Split into two 32-byte components and pad each to 64 characters
                    $rComponent = substr($rawSignature, 0, 64);
                    $sComponent = substr($rawSignature, 64);
                    
                    $rPadded = str_pad($rComponent, 64, '0', STR_PAD_LEFT);
                    $sPadded = str_pad($sComponent, 64, '0', STR_PAD_LEFT);
                    
                    $rawSignature = $rPadded . $sPadded;
                }
                
                return $this->withSign($rawSignature);
            },
        );
    }

    /**
     * @return string Hash for event.id
     *
     * @throws Throwable
     */
    public function hash(): string
    {
        throw_unless(isset($this->pubkey));

        $json = json_encode([
            0,
            $this->pubkey,
            $this->created_at,
            $this->kind,
            collect($this->tags)->toArray(),
            $this->content,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash(algo: 'sha256', data: $json);
    }

    public function rootId(): ?string
    {
        $root = collect($this->tags)
            ->first(fn (array $tag) => head($tag) === 'e' && last($tag) === 'root');

        return $root[1] ?? null;
    }

    public function replyId(): ?string
    {
        $root = collect($this->tags)
            ->first(fn (array $tag) => head($tag) === 'e' && last($tag) === 'reply');

        return $root[1] ?? null;
    }

    /**
     * @return array{
     *     kind: int|Kind,
     *     content: string,
     *     created_at: int,
     *     tags: array,
     *     id?: int,
     *     pubkey?: string,
     *     sig?: string
     * }
     */
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
