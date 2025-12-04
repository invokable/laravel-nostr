<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// vendor/bin/testbench nostr:keys
Artisan::command('nostr:keys', function () {
    $response = Nostr::key()->generate();
    $this->info($response->collect()->toPrettyJson());
});

// vendor/bin/testbench nostr:list
Artisan::command('nostr:list', function () {
    $response = Nostr::event()->list(Filter::make(kinds: [Kind::Text], limit: 5));
    $this->info($response->collect()->toPrettyJson());
});
