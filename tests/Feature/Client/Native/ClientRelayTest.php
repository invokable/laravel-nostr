<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientRelayTest extends TestCase
{
    public function test_info()
    {
        $response = Nostr::relay()->info(relays: config('nostr.relays'));

        $errors = collect($response)->whereNull('name')->toArray();
        if (filled($errors)) {
            dump($errors);
        }

        $this->assertIsArray($response);
    }
}
