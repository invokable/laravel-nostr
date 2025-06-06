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

class NativeNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_notification()
    {
        config(['nostr.driver' => 'native']);

        Http::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: ['wss://relay']))
            ->notify(new NativeTestNotification(content: 'test', tags: []));

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'wss://relay';
        });
    }

    public function test_notification_empty_relays()
    {
        Http::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: []))
            ->notify(new NativeTestNotification(content: 'test'));

        Http::assertSentCount(0);
    }

    public function test_notification_failed()
    {
        config(['nostr.driver' => 'native']);

        Http::fake([
            '*' => Http::response('', 500),
        ]);

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: ['wss://relay']))
            ->notify(new NativeTestNotification(content: 'test'));

        Http::assertSentCount(1);
    }

    public function test_notification_fake()
    {
        Notification::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk'))
            ->notify(new NativeTestNotification(content: 'test'));

        Notification::assertSentOnDemand(NativeTestNotification::class);
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
        config(['nostr.driver' => 'native']);

        Http::fake();

        $user = new NativeTestUser;

        $user->notify(new NativeTestNotification(content: 'test', tags: []));

        Http::assertSentCount(1);
    }
}

class NativeTestNotification extends \Illuminate\Notifications\Notification
{
    public function __construct(
        protected string $content,
        protected array $tags = [],
    ) {}

    public function via(object $notifiable): array
    {
        return [NostrChannel::class];
    }

    public function toNostr(object $notifiable): NostrMessage
    {
        return NostrMessage::create(content: $this->content, kind: 1, tags: $this->tags);
    }
}

class NativeTestUser extends Model
{
    use Notifiable;

    public function routeNotificationForNostr($notification): NostrRoute
    {
        return NostrRoute::to(sk: 'sk', relays: ['wss://relay']);
    }
}
