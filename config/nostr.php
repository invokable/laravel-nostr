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

        'wss://relay.damus.io',
        'wss://nos.lol',
        'wss://nostr.mom',
        'wss://relay.nostr.bg',
        'wss://nostr.oxtr.dev',
        'wss://relay.nostr.net',
        'wss://relay.primal.net',
        'wss://nostr.fmt.wiz.biz',
        'wss://relay.mutinywallet.com',
        'wss://nostr.bitcoiner.social',
        'wss://nostr-pub.wellorder.net',
        'wss://nostr.einundzwanzig.space',

        'wss://nostr.fediverse.jp',
        'wss://relay.nostr.wirednet.jp',
        'wss://nostr-relay.nokotaro.com',
        'wss://relay-jp.nostr.wirednet.jp',
    ],
];
