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

        'wss://nostr-pub.wellorder.net',
        'wss://nos.lol',
        'wss://relay.snort.social',
        'wss://relay.current.fyi',
        'wss://eden.nostr.land',
        'wss://brb.io',
        'wss://nostr.orangepill.dev',
        'wss://nostr.relayer.se',
        'wss://nostr.fmt.wiz.biz',
        'wss://relay.nostr.info',
        'wss://offchain.pub',
        'wss://nostr.shawnyeager.net',
        'wss://relay.nostr.vision',
        'wss://nostr.zkid.social',
        'wss://relay.nostr.band',
        'wss://nostr.shawnyeager.net',
        'wss://global.relay.red',
        'wss://nostr.lnprivate.network',

        'wss://nostr-relay.nokotaro.com',
        'wss://nostr.fediverse.jp',
        'wss://nostr.h3z.jp',
        'wss://relay-jp.nostr.wirednet.jp',
        'wss://relay.nostr.wirednet.jp',
        'wss://nostr.holybea.com',
    ],
];
