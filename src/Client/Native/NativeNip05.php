<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use JetBrains\PhpStorm\ArrayShape;

class NativeNip05
{
    use Conditionable;
    use Macroable;

    #[ArrayShape([
        'user' => 'string',
        'pubkey' => 'string',
        'relays' => 'array',
    ])]
    public function profile(string $user): array
    {
        [$name, $domain] = Str::of($user)
            ->whenContains(
                needles: '@',
                callback: fn (Stringable $user): array => $user->explode('@')->toArray(),
                default: fn (Stringable $domain): array => ['_', $domain->value()],
            );

        $res = Http::withOptions(['allow_redirects' => false])
            ->get('https://'.$domain.'/.well-known/nostr.json', [
                'name' => $name,
            ]);

        $pubkey = $res->json("names.$name") ?? '';
        $relays = $res->json("relays.$pubkey") ?? [];

        return compact(['user', 'pubkey', 'relays']);
    }
}
