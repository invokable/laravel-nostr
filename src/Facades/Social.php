<?php

namespace Revolution\Nostr\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;
use Revolution\Nostr\Event;
use Revolution\Nostr\Profile;
use Revolution\Nostr\Social\SocialClient;

/**
 * Implementation for social networking.
 *
 * @method static static withRelay(string $relay)
 * @method static static withKey(string $sk, string $pk)
 * @method static Response publishEvent(Event $event)
 * @method static array createNewUser(Profile $profile)
 * @method static Response updateProfile(Profile $profile)
 * @method static array profile(?string $pk = null)
 * @method static array follows()
 * @method static Response updateFollows(array $follows)
 * @method static array relays()
 * @method static Response updateRelays(array $relays = [])
 * @method static array profiles(array $authors)
 * @method static array notes(array $authors, ?int $since = null, ?int $until = null, ?int $limit = null)
 * @method static array mergeNotesAndProfiles(array $notes, array $profiles)
 * @method static array timeline(?int $since = null, ?int $until = null, ?int $limit = 10)
 * @method static Response createNote(string $content, array $tags = [])
 * @method static Response createNoteTo(string $content, string $pk)
 * @method static Response createNoteWithHashTag(string $content, array $hashtags = [])
 * @method static Response reply(Event $event, string $content, array $mentions = [], array $hashtags = [])
 * @method static Response reaction(Event $event, string $content = '+')
 * @method static Response delete(string $event_id)
 * @method static Event getEventById(string $id)
 *
 * @see SocialClient
 */
class Social extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SocialClient::class;
    }
}
