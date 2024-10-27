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

class NodeNotificationTest extends TestCase
{
    public function test_notification()
    {
        config(['nostr.driver' => 'node']);

        Http::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: ['wss://']))
            ->notify(new NodeTestNotification(content: 'test', tags: []));

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) {
            //dump($request);
            return $request['event']['content'] === 'test' &&
                $request['sk'] === 'sk' &&
                $request['relay'] === 'wss://';
        });
    }

    public function test_notification_empty_relays()
    {
        config(['nostr.driver' => 'node']);

        Http::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: []))
            ->notify(new NodeTestNotification(content: 'test'));

        Http::assertSentCount(0);
    }

    public function test_notification_failed()
    {
        config(['nostr.driver' => 'node']);

        Http::fake([
            '*' => Http::response('', 500),
        ]);

        Notification::route('nostr', NostrRoute::to(sk: 'sk', relays: ['wss://relay']))
            ->notify(new NodeTestNotification(content: 'test'));

        Http::assertSentCount(1);
    }

    public function test_notification_fake()
    {
        config(['nostr.driver' => 'node']);

        Notification::fake();

        Notification::route('nostr', NostrRoute::to(sk: 'sk'))
            ->notify(new NodeTestNotification(content: 'test'));

        Notification::assertSentOnDemand(NodeTestNotification::class);
    }

    public function test_user_notify()
    {
        config(['nostr.driver' => 'node']);

        Http::fake();

        $user = new NodeTestUser();

        $user->notify(new NodeTestNotification(content: 'test', tags: []));

        Http::assertSentCount(1);
    }
}

class NodeTestNotification extends \Illuminate\Notifications\Notification
{
    public function __construct(
        protected string $content,
        protected array $tags = [],
    ) {
    }

    public function via(object $notifiable): array
    {
        return [NostrChannel::class];
    }

    public function toNostr(object $notifiable): NostrMessage
    {
        return NostrMessage::create(content: $this->content, kind: 1, tags: $this->tags);
    }
}

class NodeTestUser extends Model
{
    use Notifiable;

    public function routeNotificationForNostr($notification): NostrRoute
    {
        return NostrRoute::to(sk: 'sk', relays: ['wss://relay']);
    }
}
