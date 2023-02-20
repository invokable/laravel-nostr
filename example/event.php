<?php

declare(strict_types=1);

use Revolution\Nostr\Event;
use Revolution\Nostr\Kind;

require_once '../vendor/autoload.php';

$e = new Event(
    kind: Kind::Text,
    content: 'test',
    created_at: now()->timestamp,
    tags: [['e', 'test']],
);

$e->withId(id: 'id')->withPublicKey(pubkey: 'pub')->withSign(sig: 'sig');

var_dump($e->toJson());
