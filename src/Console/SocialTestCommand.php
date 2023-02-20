<?php

namespace Revolution\Nostr\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Revolution\Nostr\Facades\Social;
use Revolution\Nostr\Profile;
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

        $profile = new Profile(
            name: 'test',
            display_name: 'test1',
            about: 'about',
        );

        $res = Social::updateProfile(
            profile: $profile,
        );

        dump($res->json());

        $res = Social::profile(
            pk: $pk,
        );

        dump($res->json());

        $follow_ids = Social::follows();
        dump($follow_ids);

        $follows = collect($follow_ids)->push($pk)->unique()->map(fn ($follow) => new PersonTag(pubkey: $follow))->toArray();

        $res = Social::updateFollows(follows: $follows);
        dump($res->json());

        $profiles = Social::profiles(authors: $follow_ids);
        dump($profiles);

        $notes = Social::notes(authors: $follow_ids, limit: 2);
        dump($notes);

        $texts = Social::mergeNotesAndProfiles($notes, $profiles);
        dump($texts);

        $next_until = Arr::get(last($texts), 'created_at') - 1;
        dump($next_until);
        $next_notes = Social::notes(authors: $follow_ids, until: $next_until, limit: 1);
        dump($next_notes);

        $next_texts = Social::mergeNotesAndProfiles($next_notes, $profiles);
        dump($next_texts);

        $timelines = Social::timeline();
        //dump($timelines);
        foreach ($timelines as $timeline) {
            dump(Carbon::createFromTimestamp($timeline['created_at'])->toDateTimeString());
        }

        //
        //                $res = Social::createTextNote('test');
        //                dump($res->json());

        return 0;
    }
}
