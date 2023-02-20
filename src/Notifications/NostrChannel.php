<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\Notification;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Kind;

class NostrChannel
{
    /**
     * @throws RequestException
     */
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

        $event = new Event(
            kind: Kind::Text,
            content: $message->content,
            created_at: now()->timestamp,
            tags: $message->tags,
        );

        $response = Nostr::event()->publish(
            event: $event,
            sk: $route->sk,
            relay: $route->relay
        );

        $response->throwIf($response->failed());
    }
}
