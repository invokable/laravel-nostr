Laravel Notifications
====

## Notification class
```php
use Illuminate\Notifications\Notification;
use Revolution\Nostr\Notifications\NostrChannel;
use Revolution\Nostr\Notifications\NostrMessage;
use Revolution\Nostr\Tags\HashTag;

class TestNotification extends Notification
{
    public function via($notifiable): array
    {
        return [
            'mail',
            NostrChannel::class
        ];
    }

    public function toNostr(mixed $notifiable): NostrMessage
    {
        return new NostrMessage(
            content: 'hello #laravel',
            tags: [
                HashTag::make(t: 'laravel'),
            ],
        );
    }
}
```

## On-Demand Notifications
```php
use Illuminate\Support\Facades\Notification;
use Revolution\Nostr\Notifications\NostrRoute;

Notification::route('nostr', NostrRoute::to(sk: 'sk'))
            ->notify(new TestNotification());
```

## User Notifications
```php
use Illuminate\Notifications\Notifiable;
use Revolution\Nostr\Notifications\NostrRoute;

class User
{
    use Notifiable;

    public function routeNotificationForNostr($notification): NostrRoute
    {
        return NostrRoute::to(sk: $this->sk, relays: ['wss://']);
    }
}
```

```php
$user->notify(new TestNotification());
```

## Which relay servers are used?

All relays in `config/nostr.php`.

## Change relay server at runtime
Specify relays in NostrRoute.

```php
use Revolution\Nostr\Notifications\NostrRoute;

return NostrRoute::to(sk: 'sk', relays: ['wss://', 'wss://']);
```
