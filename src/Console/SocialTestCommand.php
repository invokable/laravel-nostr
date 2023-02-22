<?php

namespace Revolution\Nostr\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Revolution\Nostr\Event;
use Revolution\Nostr\Exceptions\EventNotFoundException;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Facades\Social;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Profile;
use Revolution\Nostr\Tag\EventTag;
use Revolution\Nostr\Tag\PersonTag;

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
        $res = Social::profile(
            pk: $pk,
        );

        dump($res->json());
        //
        //        $follow_ids = Social::follows();
        //        dump($follow_ids);
        //
        //        $follows = collect($follow_ids)->push($pk)->unique()->map(fn ($follow) => new PersonTag(p: $follow))->toArray();
        //
        //        $res = Social::updateFollows(follows: $follows);
        //        dump($res->json());
        //
        //        $profiles = Social::profiles(authors: $follow_ids);
        //        dump($profiles);
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
        //                $res = Social::createTextNote('test');
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

        //        $res = Social::createTextNote('test');
        //        $event_id_p = Arr::get($res, 'event.id');
        //
        //        $res = Social::reply(content: 'reply1', event_id: $event_id_p, to: [$pk]);
        //        $event_id = Arr::get($res, 'event.id');
        //        dump($res->json());
        //
        //        $res = Social::reply(content: 'reply2', event_id: $event_id_p, to: [$pk]);
        //        $event_id = Arr::get($res, 'event.id');
        //        dump($res->json());
        //
        //        $res = Social::reply(content: 'reply3', event_id: $event_id_p);
        //        $event_id = Arr::get($res, 'event.id');
        //        dump($res->json());

        try {
            $event = Social::getEventById(id: '1');
            dump($event->toJson());
        } catch (EventNotFoundException $e) {
            dump($e->getMessage());
        }

        return 0;
    }
}
