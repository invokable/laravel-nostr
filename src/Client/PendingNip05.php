<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Macroable;
use JetBrains\PhpStorm\ArrayShape;

class PendingNip05
{
    use Macroable;

    /**
     * @throws RequestException
     */
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
                                  default: fn (Stringable $domain): array => ['_', $domain->value()]
                              );

        $res = Http::withOptions(['allow_redirects' => false])
                   ->get("https://$domain/.well-known/nostr.json", [
                       'name' => $name,
                   ])
                   ->throw();

        $pubkey = $res->json("names.$name");
        $relays = $res->json("relays.$pubkey") ?? [];

        return compact(['user', 'pubkey', 'relays']);
    }
}
