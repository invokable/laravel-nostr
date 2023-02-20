<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Concerns\HasHttp;

class PendingNip05
{
    use HasHttp;
    use Macroable;

    public function profile(string $user): Response
    {
        return $this->http()->post('nip05/profile', [
            'user' => $user,
        ]);
    }
}
