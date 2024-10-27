<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;

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

        $route->relays = $route->relays ?? Config::get('nostr.relays');

        if (blank($route->relays)) {
            return;
        }

        $this->publish($message, $route);
    }

    protected function publish(NostrMessage $message, NostrRoute $route): void
    {
        $event = Event::make(
            kind: $message->kind,
            content: $message->content,
            created_at: now()->timestamp,
            tags: collect($message->tags)->toArray(),
        );

        Nostr::pool()
             ->withRelays($route->relays)
             ->publish(
                 event: $event,
                 sk: $route->sk,
             );
    }
}
