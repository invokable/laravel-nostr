<?php

namespace Tests\Feature\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Request;
use Illuminate\Notifications\Notifiable;
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
        Http::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: ['wss://']))
                    ->notify(new TestNotification(content: 'test', tags: []));

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) {
            return $request['event']['content'] === 'test' &&
                $request['sk'] === 'sk' &&
                $request['relay'] === 'wss://';
        });
    }

    public function test_notification_empty_relays()
    {
        Http::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: []))
                    ->notify(new TestNotification(content: 'test'));

        Http::assertSentCount(0);
    }

    public function test_notification_failed()
    {
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: ['wss://']))
                    ->notify(new TestNotification(content: 'test'));

        Http::assertSentCount(1);
    }

    public function test_notification_fake()
    {
        Notification::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk'))
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
        $r = new NostrRoute(sk: 'sk');

        $this->assertSame('sk', $r->sk);
    }

    public function test_user_notify()
    {
        Http::fake();

        $user = new TestUser();

        $user->notify(new TestNotification(content: 'test', tags: []));

        Http::assertSentCount(1);
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

class TestUser extends Model
{
    use Notifiable;

    public function routeNotificationForNostr($notification): NostrRoute
    {
        return NostrRoute::to(sk: 'sk', relays: ['wss://']);
    }
}
