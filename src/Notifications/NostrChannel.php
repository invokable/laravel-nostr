<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
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

        $route->relays = $route->relays ?? Config::get('nostr.relays');

        if (blank($route->relays)) {
            return;
        }

        $this->publish($message, $route);
    }

    protected function publish(NostrMessage $message, NostrRoute $route): void
    {
        $event = new Event(
            kind: Kind::Text,
            content: $message->content,
            created_at: now()->timestamp,
            tags: $message->tags,
        );

        $responses = Nostr::pool()
                          ->withRelays($route->relays)
                          ->publish(
                              event: $event,
                              sk: $route->sk,
                          );

        foreach ($responses as $relay => $response) {
            if ($response->failed()) {
                Log::debug(class_basename($this).' : '.$relay.' : '.$response->body(), $response->json() ?? []);
            }
        }
    }
}
