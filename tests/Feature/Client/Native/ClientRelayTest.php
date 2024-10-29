<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientRelayTest extends TestCase
{
    public function test_info()
    {
        if (filled(getenv('GITHUB_TOKEN'))) {
            Http::fake(fn () => Http::response(['name' => 'test']));
        }

        $response = Nostr::relay()->info(relays: config('nostr.relays'));

        $errors = collect($response)->whereNull('name')->toArray();
        if (filled($errors)) {
            dump($errors);
        }

        $this->assertIsArray($response);
    }
}
