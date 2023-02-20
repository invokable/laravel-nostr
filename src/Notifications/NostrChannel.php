<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Kind;

class NostrChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        /** @var NostrMessage $message */
        $message = $notification->toNostr($notifiable);

        if (! $message instanceof NostrMessage) {
            return; // @codeCoverageIgnore
        }

        /** @var NostrRoute $route */
        $route = $notifiable->routeNotificationFor('nostr', $notification);

        if (! $route instanceof NostrRoute) {
            return; // @codeCoverageIgnore
        }

        $route->relays = $route->relays ?? Config::get('nostr.relays');

        if (blank($route->relays)) {
            return;
        }

        $event = new Event(
            kind: Kind::Text->value,
            content: $message->content,
            created_at: now()->timestamp,
            tags: $message->tags,
        );

        Nostr::pool()
             ->withRelays($route->relays)
             ->publish(
                 event: $event,
                 sk: $route->sk,
             );
    }
}
