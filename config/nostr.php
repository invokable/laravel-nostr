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

        'wss://brb.io',
        'wss://nos.lol',
        'wss://offchain.pub',
        'wss://eden.nostr.land',
        'wss://nostr.relayer.se',
        'wss://relay.nostr.info',
        'wss://relay.nostr.band',
        'wss://global.relay.red',
        'wss://nostr.fmt.wiz.biz',
        'wss://relay.current.fyi',
        'wss://nostr.zkid.social',
        'wss://relay.snort.social',
        'wss://relay.nostr.vision',
        'wss://nostr.orangepill.dev',
        'wss://nostr.shawnyeager.net',
        'wss://nostr.lnprivate.network',
        'wss://nostr-pub.wellorder.net',

        'wss://nostr.h3z.jp',
        'wss://nostr.holybea.com',
        'wss://nostr.fediverse.jp',
        'wss://relay.nostr.wirednet.jp',
        'wss://nostr-relay.nokotaro.com',
        'wss://relay-jp.nostr.wirednet.jp',
    ],
];
