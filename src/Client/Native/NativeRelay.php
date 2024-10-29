<?php

namespace Revolution\Nostr\Client\Native;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NativeRelay
{
    /**
     * @param  array<string>|string  $relays
     * @return array<array-key, array>
     */
    public function info(array|string $relays): array
    {
        $relays = Collection::wrap($relays)
            ->mapWithKeys(
                fn (string $relay) => [
                    $relay => Str::of($relay)
                        ->chopStart(['wss://', 'ws://'])
                        ->prepend('https://')
                        ->toString(),
                ],
            );

        $responses = Http::pool(
            fn (Pool $pool) => $relays->map(
                fn (string $relay, string $ws) => $pool->as($ws)
                    ->withHeader('Accept', 'application/nostr+json')
                    ->get($relay),
            ));

        return collect($responses)->map(function (mixed $response) {
            if ($response instanceof Response && $response->successful()) {
                return $response->json();
            } elseif ($response instanceof \Exception) {
                return ['error' => $response->getMessage()];
            } else {
                return ['error' => 'error'];
            }
        })->toArray();
    }
}
