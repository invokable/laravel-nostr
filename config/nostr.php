<?php

return [
    /**
     * @see https://github.com/kawax/nostr-vercel-api
     */
    'api_base' => env('NOSTR_API_BASE', 'https://nostr-api.vercel.app/api/'),

    /**
     * The first relay is used as the primary relay.
     */
    'relays' => [
        'wss://relay.damus.io',

        'wss://nos.lol',
        'wss://nostr.mom',
        'wss://offchain.pub',
        'wss://relay.nostr.band',
        'wss://nostr.fmt.wiz.biz',
        'wss://nostr-pub.wellorder.net',

        'wss://relay.plebstr.com',
        'wss://nostr.fediverse.jp',
        'wss://relay.nostr.wirednet.jp',
        'wss://nostr-relay.nokotaro.com',
        'wss://relay-jp.nostr.wirednet.jp',
    ],
];
