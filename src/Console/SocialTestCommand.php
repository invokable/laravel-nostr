<?php

namespace Revolution\Nostr\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Event;
use Revolution\Nostr\Exceptions\EventNotFoundException;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Facades\Social;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Profile;
use Revolution\Nostr\Tags\EventTag;
use Revolution\Nostr\Tags\PersonTag;

class SocialTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Social Test Command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! app()->runningUnitTests()) {
            $this->error('Testing only.');

            return 1;
        }

        $sk = env('NOSTR_SK');
        $pk = env('NOSTR_PK');

        //dump($sk, $pk);

        Social::withKey(sk: $sk, pk: $pk);

        //        $profile = new Profile(
        //            name: 'test',
        //            display_name: 'test1',
        //            about: 'about',
        //        );
        //
        //        $res = Social::updateProfile(
        //            profile: $profile,
        //        );
        //
        //        dump($res->json());
        //
        //                $res = Social::profile();
        //
        //                dump($res);
        //
        //                $follow_ids = Social::follows();
        //                dump($follow_ids);
        //
        //        $follows = collect($follow_ids)->push($pk)->unique()->map(fn ($follow) => new PersonTag(p: $follow))->toArray();
        //
        //        $res = Social::updateFollows(follows: $follows);
        //        dump($res->json());
        //
        //                $profiles = Social::profiles(authors: $follow_ids);
        //                dump($profiles);
        //
        //        $notes = Social::notes(authors: $follow_ids, limit: 2);
        //        dump($notes);
        //
        //        $texts = Social::mergeNotesAndProfiles($notes, $profiles);
        //        dump($texts);
        //
        //        $next_until = Arr::get(last($texts), 'created_at') - 1;
        //        dump($next_until);
        //        $next_notes = Social::notes(authors: $follow_ids, until: $next_until, limit: 1);
        //        dump($next_notes);
        //
        //        $next_texts = Social::mergeNotesAndProfiles($next_notes, $profiles);
        //        dump($next_texts);
        //
        //        $timelines = Social::timeline();
        //dump($timelines);
        //        foreach ($timelines as $timeline) {
        //            dump(Carbon::createFromTimestamp($timeline['created_at'])->toDateTimeString());
        //        }

        //
        //                $res = Social::createNote('test');
        //                dump($res->json());

        //        $event = new Event(
        //            kind: Kind::Text,
        //            content: 'publish test',
        //            created_at: now()->timestamp,
        //            tags: [],
        //        );
        //
        //        $responses = Nostr::pool()->publish(event: $event, sk: $sk);
        //        foreach ($responses as $relay => $response) {
        //            $this->line($relay);
        //
        //            $this->table(
        //                ['kind', 'content', 'created_at'],
        //                [$response->collect('event')->only(['kind', 'content', 'created_at'])->toArray()]
        //            );
        //        }
        //
        //        $event_id = Arr::first($responses)['event']['id'];
        //        $this->info($event_id);

        //        $delete = new Event(
        //            kind: Kind::EventDeletion,
        //            content: '',
        //            created_at: now()->timestamp,
        //            tags: [(new EventTag(id: $event_id))->toArray()],
        //        );
        //
        //        $responses = Nostr::pool()->publish(event: $delete, sk: $sk);
        //        foreach ($responses as $relay => $response) {
        //            $this->line($relay);
        //            $this->info($response->body());
        //        }

        //        $res = Social::createNote('test');
        //
        //        $parent_event = Event::fromArray($res->json('event'));
        //
        //        $res = Social::reply(event: $parent_event, content: 'reply1', mentions: [$pk]);
        //        dump($res->json());
        //
        //        $res = Social::reply(event: $parent_event, content: 'reply2', mentions: [$pk]);
        //        dump($res->json());
        //
        //        $reply2_event = Event::fromArray($res->json('event'));
        //
        //        $res = Social::reply(event: $reply2_event, content: 'reply3', hashtags: ['test']);
        //        dump($res->json());
        //
        //        $res = Social::reaction(event: $parent_event, content: '+');
        //        dump($res->json());
        //
        //        $res = Social::reaction(event: $reply2_event, content: '+');
        //        dump($res->json());

        //        try {
        //            $event = Social::getEventById(id: '');
        //            dump($event->toJson());
        //
        //            $res = Nostr::event()->verify($event);
        //            dump($res->json());
        //        } catch (EventNotFoundException $e) {
        //            dump($e->getMessage());
        //        }

        //        $filter = new Filter(
        //            authors: [$pk],
        //        );
        //
        //        Nostr::pool()->list([$filter]);

        //                $keys = Nostr::key()->generate()->json();
        //                dump($keys);
        //
        //                $keys_sk = Nostr::key()->fromSecretKey($keys['sk'])->json();
        //                dump($keys_sk);
        //
        //                $keys_nsec = Nostr::key()->fromNsec($keys['nsec'])->json();
        //                dump($keys_nsec);
        //
        //                $res = Http::baseUrl(Config::get('nostr.api_base'))
        //                           ->get('key/from_pk', [
        //                               'pk' => $keys['pk'],
        //                           ]);
        //                dump($res->json());

        //                $res = Nostr::nip19()->decode('');
        //                dump($res->json());
        //
        //                $res = Nostr::nip19()->note('');
        //                dump($res->json());
        //
        //                $res = Nostr::nip19()->nprofile([
        //                    'pubkey' => '11',
        //                    'relays' => [],
        //                ]);
        //                dump($res->json());
        //
        //                $res = Nostr::nip19()->nevent(json_decode('{}',true));
        //                dump($res->json());

        //        $res = Nostr::nip19()->naddr([
        //            'identifier' => '1',
        //            'pubkey' => '11',
        //            'kind' => 0,
        //        ]);
        //        dump($res->json());

        $encrypt = Nostr::nip04()->encrypt($sk, $pk, 'テスト'.now()->toDateTimeString());
        dump($dm = $encrypt->json('encrypt'));

        $e = Event::make(
            kind: Kind::EncryptedDirectMessage,
            content: $dm,
            created_at: now()->timestamp,
            tags: [PersonTag::make(p: $pk)],
        );

        $r = Nostr::event()->publish(event: $e, sk: $sk);
        dump($r->json());

        $f = Filter::make(
            kinds: [Kind::EncryptedDirectMessage],
            limit: 1,
        )->with(['#p' => [$pk]]);

        $r_e = Nostr::event()->get(filter: $f, relay: Arr::first(Config::get('nostr.relays')));
        dump($r_e->json());

        $decrypt = Nostr::nip04()->decrypt($sk, $pk, $r_e->json('event.content'));
        dump($decrypt->json('decrypt'));

        //        $res = Social::updateRelays();
        //        dump($res->body());

        //        $res = Social::relays();
        //        dump($res);

        return 0;
    }
}
