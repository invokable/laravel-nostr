<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use BitWasp\Bech32\Exception\Bech32Exception;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Nip19\AddressPointer;
use Revolution\Nostr\Nip19\EventPointer;
use Revolution\Nostr\Nip19\ProfilePointer;
use swentel\nostr\Event\Event;
use swentel\nostr\Event\Profile\Profile;
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
     * @throws Bech32Exception|BindingResolutionException
     */
    public function note(string $id): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        return $this->response($nip19->encodeNote($id));
    }

    /**
     * encode profile.
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function nprofile(ProfilePointer $profile): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        $profileObj = new Profile;
        $profileObj->setPublicKey($profile->toArray()['pubkey']);

        return $this->response($nip19->encodeProfile($profileObj, $profile->toArray()['relays'] ?? []));
    }

    /**
     * encode event.
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function nevent(EventPointer $event): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        $eventObj = new Event;
        $eventData = $event->toArray();
        $eventObj->setId($eventData['id']);
        if (! empty($eventData['author'])) {
            $eventObj->setPublicKey($eventData['author']);
        }

        return $this->response($nip19->encodeEvent($eventObj, $eventData['relays'] ?? [], $eventData['author'] ?? ''));
    }

    /**
     * encode addr.
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function naddr(AddressPointer $addr): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        $eventObj = new Event;
        $addrData = $addr->toArray();
        $eventObj->setPublicKey($addrData['pubkey']);
        $kind = is_object($addrData['kind']) ? $addrData['kind']->value : $addrData['kind'];
        $eventObj->setKind($kind);

        return $this->response($nip19->encodeAddr($eventObj, $addrData['identifier'], $kind, $addrData['pubkey'], $addrData['relays'] ?? []));
    }
}
