<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Node;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Node\Concerns\HasHttp;

class NodeNip04
{
    use HasHttp;
    use Macroable;
    use Conditionable;

    /**
     * @param  string  $sk  sender sk
     * @param  string  $pk  receiver pk
     */
    public function encrypt(#[\SensitiveParameter] string $sk, string $pk, string $content): Response
    {
        return $this->http()
                    ->post('nip04/encrypt', compact(['sk', 'pk', 'content']));
    }

    /**
     * @param  string  $sk  receiver sk
     * @param  string  $pk  sender pk
     */
    public function decrypt(#[\SensitiveParameter] string $sk, string $pk, string $content): Response
    {
        return $this->http()
                    ->post('nip04/decrypt', compact(['sk', 'pk', 'content']));
    }
}
