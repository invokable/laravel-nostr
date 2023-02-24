<?php

declare(strict_types=1);

namespace Revolution\Nostr\Social;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Event;
use Revolution\Nostr\Exceptions\EventNotFoundException;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Profile;
use Revolution\Nostr\Tags\EventTag;
use Revolution\Nostr\Tags\HashTag;
use Revolution\Nostr\Tags\PersonTag;

/**
 * Implementation for social networking.
 */
class SocialClient
{
    use Macroable, Conditionable;

    protected string $relay;
    protected string $sk;
    protected string $pk;

    public function __construct()
    {
        $this->relay = Arr::first((Config::get('nostr.relays')));
    }

    public function withRelay(string $relay): static
    {
        $this->relay = $relay;

        return $this;
    }

    public function withKey(string $sk = '', string $pk = ''): static
    {
        $this->sk = $sk;
        $this->pk = $pk;

        return $this;
    }

    public function publishEvent(Event $event): Response
    {
        return Nostr::event()->publish(event: $event, sk: $this->sk, relay: $this->relay);
    }

    /**
     * @throws Exception
     */
    public function createNewUser(Profile $profile): array
    {
        $keys = Nostr::key()->generate()->collect();

        if ($keys->has(['sk', 'pk'])) {
            $this->withKey(sk: $keys->get('sk'), pk: $keys->get('pk'));

            $response = $this->updateProfile($profile);

            if ($response->successful()) {
                return [
                    'keys' => $keys->toArray(),
                    'profile' => $profile->toArray(),
                ];
            }
        }

        throw new Exception('Failed create new user.');
    }

    public function updateProfile(Profile $profile): Response
    {
        $event = new Event(
            kind: Kind::Metadata,
            content: $profile->toJson(),
            created_at: now()->timestamp,
        );

        return $this->publishEvent(event: $event);
    }

    public function profile(?string $pk = null): array
    {
        $pk = $pk ?? $this->pk;

        $filter = new Filter(
            authors: [$pk],
            kinds: [Kind::Metadata],
        );

        return Nostr::event()
                    ->get(filter: $filter, relay: $this->relay)
                    ->json('event') ?? [];
    }

    public function follows(): array
    {
        $filter = new Filter(
            authors: [$this->pk],
            kinds: [Kind::Contacts],
        );

        $response = Nostr::event()->get(filter: $filter, relay: $this->relay);

        return $response->collect('event.tags')
                        ->mapToGroups(fn ($tag) => [$tag[0] => $tag[1]])
                        ->get('p')
                        ?->toArray() ?? [];
    }

    /**
     * @param  array<PersonTag|array>  $follows  Must include all follows.
     */
    public function updateFollows(array $follows): Response
    {
        $event = new Event(
            kind: Kind::Contacts,
            content: '',
            created_at: now()->timestamp,
            tags: collect($follows)->toArray(),
        );

        return $this->publishEvent(event: $event);
    }

    /**
     * @param  array<string>  $authors
     */
    public function profiles(array $authors): array
    {
        $filter = new Filter(
            authors: $authors,
            kinds: [Kind::Metadata],
        );

        return Nostr::event()
                    ->list(filters: [$filter], relay: $this->relay)
                    ->json('events') ?? [];
    }

    /**
     * @param  array<string>  $authors
     */
    public function notes(array $authors, ?int $since = null, ?int $until = null, ?int $limit = null): array
    {
        $filter = new Filter(
            authors: $authors,
            kinds: [Kind::Text],
            since: $since,
            until: $until,
            limit: $limit,
        );

        $response = Nostr::event()->list(filters: [$filter], relay: $this->relay);

        return $response->collect('events')
                        ->sortByDesc('created_at')
                        ->toArray() ?? [];
    }

    public function mergeNotesAndProfiles(array $notes, array $profiles): array
    {
        return collect($notes)
            ->filter(fn ($note) => Arr::exists($note, 'pubkey'))
            ->map(function ($note) use ($profiles) {
                $profile = collect($profiles)->firstWhere('pubkey', $note['pubkey']);

                if (! Arr::exists($profile, 'content')) {
                    return $note;
                }

                $user = json_decode(Arr::get($profile, 'content', '[]'), true);

                return array_merge($note, $user);
            })->toArray();
    }

    public function timeline(?int $since = null, ?int $until = null, ?int $limit = 10): array
    {
        $follows = $this->follows();

        $profiles = $this->profiles(authors: $follows);

        $notes = $this->notes(authors: $follows, since: $since, until: $until, limit: $limit);

        return $this->mergeNotesAndProfiles($notes, $profiles);
    }

    /**
     * If you need a more complex creation method, use macro() or publishEvent() directly.
     */
    public function createNote(string $content, array $tags = []): Response
    {
        $event = new Event(
            kind: Kind::Text,
            content: $content,
            created_at: now()->timestamp,
            tags: collect($tags)->toArray(),
        );

        return $this->publishEvent(event: $event);
    }

    public function createNoteTo(string $content, string $pk): Response
    {
        $event = new Event(
            kind: Kind::Text,
            content: $content,
            created_at: now()->timestamp,
            tags: [PersonTag::make(p: $pk)->toArray()],
        );

        return $this->publishEvent(event: $event);
    }

    public function createNoteWithHashTag(string $content, array $hashtags = []): Response
    {
        $tags = collect();

        foreach ($hashtags as $hashtag) {
            $tags->push(HashTag::make(t: $hashtag)->toArray());
        }

        $event = new Event(
            kind: Kind::Text,
            content: $content,
            created_at: now()->timestamp,
            tags: $tags->toArray(),
        );

        return $this->publishEvent(event: $event);
    }

    public function reply(string $content, string $event_id, array $to = [], string $marker = 'root'): Response
    {
        $tags = collect([
            new EventTag(
                id: $event_id,
                relay: $this->relay,
                marker: $marker,
            ),
        ]);

        foreach ($to as $pk) {
            $tags->push(PersonTag::make(p: $pk)->toArray());
        }

        $event = new Event(
            kind: Kind::Text,
            content: $content,
            created_at: now()->timestamp,
            tags: $tags->toArray(),
        );

        return $this->publishEvent(event: $event);
    }

    public function delete(string $event_id): Response
    {
        $e = EventTag::make(id: $event_id);

        $event = new Event(
            kind: Kind::EventDeletion,
            created_at: now()->timestamp,
            tags: [$e->toArray()],
        );

        return $this->publishEvent(event: $event);
    }

    /**
     * @throws RequestException|EventNotFoundException
     */
    public function getEventById(string $id): Event
    {
        $res = Nostr::event()
                    ->get(filter: Filter::make(ids: [$id]), relay: $this->relay)
                    ->throw();

        $validator = validator(data: $event = $res->json('event') ?? [], rules: [
            'kind' => 'required|filled|numeric|integer',
            'content' => 'string',
            'created_at' => 'required|filled|numeric|integer',
            'tags' => 'array',
            'id' => 'required|filled|string|size:64',
            'pubkey' => 'required|filled|string|size:64',
            'sig' => 'required|filled|string|size:128',
        ]);

        if ($validator->fails()) {
            throw new EventNotFoundException("Event(id:$id) not found on $this->relay");
        }

        return Event::makeSigned(
            kind: $event['kind'],
            content: $event['content'] ?? '',
            created_at: $event['created_at'],
            tags: $event['tags'] ?? [],
            id: $event['id'],
            pubkey: $event['pubkey'],
            sig: $event['sig']
        );
    }
}
