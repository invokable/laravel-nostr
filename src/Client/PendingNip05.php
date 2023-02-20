<?php
declare(strict_types=1);

namespace Revolution\Nostr\Client;

use Illuminate\Http\Client\Response;
use Revolution\Nostr\Client\Concerns\HasHttp;

class PendingNip05
{
    use HasHttp;

    public function profile(string $user): Response
    {
        return $this->http()->post('nip05/profile', [
            'user' => $user,
        ]);
    }
}
