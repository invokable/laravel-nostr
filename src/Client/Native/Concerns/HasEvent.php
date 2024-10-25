<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native\Concerns;

use Revolution\Nostr\Event;
use swentel\nostr\Event\Event as NativeEvent;
use swentel\nostr\Sign\Sign;

use function Illuminate\Support\enum_value;

trait HasEvent
{
    /**
     * @param  Event  $event  Unsigned or Signed Event
     */
    protected function toNativeEvent(Event $event): NativeEvent
    {
        $n_event = (new NativeEvent())
            ->setKind(enum_value($event->kind))
            ->setContent($event->content)
            ->setTags($event->tags);

        if (! empty($event->pubkey)) {
            $n_event->setPublicKey($event->pubkey);
        }

        if (! empty($event->created_at)) {
            $n_event->setCreatedAt($event->created_at);
        }

        if (! empty($event->id)) {
            $n_event->setId($event->id);
        }

        if (! empty($event->sig)) {
            $n_event->setSignature($event->sig);
        }

        return $n_event;
    }

    /**
     * @param  Event  $event  Unsigned Event
     * @return NativeEvent Signed Event
     */
    protected function toSignedNativeEvent(Event $event, #[\SensitiveParameter] string $sk): NativeEvent
    {
        $n_event = $this->toNativeEvent($event);

        $signer = app(Sign::class);
        $signer->signEvent($n_event, $sk);

        return $n_event;
    }
}
