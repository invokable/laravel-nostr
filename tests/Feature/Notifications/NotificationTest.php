<?php

namespace Tests\Feature\Notifications;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Revolution\Nostr\Notifications\NostrChannel;
use Revolution\Nostr\Notifications\NostrMessage;
use Revolution\Nostr\Notifications\NostrRoute;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function test_notification()
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relay: 'wss://'))
                    ->notify(new TestNotification(content: 'test', tags: []));

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) {
            return $request['event']['content'] === 'test' &&
                $request['sk'] === 'sk' &&
                $request['relay'] === 'wss://';
        });
    }

    public function test_notification_throw()
    {
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $this->expectException(RequestException::class);

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relay: 'wss://'))
                    ->notify(new TestNotification(content: 'test'));
    }

    public function test_notification_fake()
    {
        Notification::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relay: 'wss://'))
                    ->notify(new TestNotification(content: 'test'));

        Notification::assertSentOnDemand(TestNotification::class);
    }

    public function test_message()
    {
        $m = new NostrMessage(content: 'test', tags: []);

        $this->assertIsArray($m->toArray());
    }

    public function test_route()
    {
        $r = new NostrRoute(sk: 'sk', relay: 'wss://');

        $this->assertIsArray($r->toArray());
    }
}

class TestNotification extends \Illuminate\Notifications\Notification
{
    public function __construct(
        protected string $content,
        protected array $tags = [],
    ) {
    }

    public function via($notifiable): array
    {
        return [NostrChannel::class];
    }

    public function toNostr($notifiable)
    {
        return NostrMessage::create(content: $this->content, tags: $this->tags);
    }
}
