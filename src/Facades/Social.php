<?php

namespace Revolution\Nostr\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;
use Revolution\Nostr\Event;
use Revolution\Nostr\Profile;
use Revolution\Nostr\Social\SocialClient;

/**
 * @method static static withRelay(string $relay)
 * @method static static withKey(string $sk, string $pk)
 * @method static Response publishEvent(Event $event)
 * @method static array createNewUser(Profile $profile)
 * @method static Response updateProfile(Profile $profile)
 * @method static Response profile(?string $pk = null)
 * @method static array follows()
 * @method static Response updateFollows(array $follows)
 * @method static array profiles(array $authors)
 * @method static array notes(array $authors, ?int $since = null, ?int $until = null, ?int $limit = null)
 * @method static array mergeNotesAndProfiles(array $notes, array $profiles)
 * @method static array timeline(?int $since = null, ?int $until = null, ?int $limit = 10)
 * @method static Response createTextNote(string $content, array $tags = [])
 * @method static Response createTextNoteTo(string $content, string $pk)
 * @method static Response createTextNoteWithHashTag(string $content, array $hashtags = [])
 * @method static Response reply(string $content, string $event_id, array $to = [], string $marker = 'root')
 * @method static Response delete(string $event_id)
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
