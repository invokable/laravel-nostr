<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use BitWasp\Bech32\Exception\Bech32Exception;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use swentel\nostr\Nip19\Nip19Helper;

class NativeNip19
{
    use Conditionable;
    use HasHttp;
    use Macroable;

    /**
     * Decode NIP-19 string.
     *
     * @param  string  $n  nsec, npub, note, nprofile, nevent, naddr
     *
     * @throws Exception
     */
    public function decode(string $n): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        return $this->response($nip19->decode($n));
    }

    /**
     * encode note id.
     *
     * @throws Bech32Exception
     */
    public function note(string $id): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        return $this->response($nip19->encodeNote($id));
    }
}
