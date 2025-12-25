<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Illuminate\Support\Arr;

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
    $this->info($response->collect()->toPrettyJson(JSON_UNESCAPED_UNICODE));
});

// vendor/bin/testbench nostr:info
Artisan::command('nostr:info', function () {
    $responses = Nostr::relay()->info(config('nostr.relays'));
    foreach ($responses as $relay => $response) {
        if (Arr::exists($response, 'error')) {
            $this->error("Relay: $relay Error: {$response['error']}");
        } else {
            // dump($relay, $response);
        }
    }
})->purpose('Ensure Nostr relays are reachable and display their information.');
