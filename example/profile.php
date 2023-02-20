<?php

declare(strict_types=1);

use Revolution\Nostr\Profile;

require_once '../vendor/autoload.php';

$p = new Profile(
    name: 'name',
);

$p->about = 'about';

var_dump($p->toJson());
var_dump((string) $p);
