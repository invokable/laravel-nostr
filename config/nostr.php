<?php

return [
    /**
     * Supported: "node", "native".
     */
    'driver' => env('NOSTR_DRIVER', 'native'),

    /**
     * For node driver.
     *
     * @see https://github.com/kawax/nostr-vercel-api
     */
    'api_base' => env('NOSTR_API_BASE', 'https://nostr-api.vercel.app/api/'),

    /**
     * The first relay is used as the primary relay.
     */
    'relays' => [
        // 'wss://relay.nostr.band',

        'wss://x.kojira.io',

        'wss://relay.damus.io',
        'wss://nos.lol',
        'wss://nostr.oxtr.dev',
        'wss://relay.nostr.net',
        'wss://relay.primal.net',
        'wss://relay.snort.social',
        'wss://nostr.bitcoiner.social',
        'wss://nostr-pub.wellorder.net',
        'wss://nostr.einundzwanzig.space',

        'wss://yabu.me',
        'wss://relay.barine.co',
        'wss://nostr-relay.h3z.jp',
        'wss://nostr.fediverse.jp',
        // 'wss://relay-jp.shino3.net',
        'wss://lang.relays.land/ja/',
        'wss://relay.nostr.wirednet.jp',
        // 'wss://nostr-relay.nokotaro.com',
        'wss://relay-jp.nostr.wirednet.jp',
    ],
];
