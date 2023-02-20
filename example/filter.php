<?php

declare(strict_types=1);

use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;

require_once '../vendor/autoload.php';

$f = new Filter(
    ids: ['a'],
    kinds: [Kind::Metadata, 1],
);

$f->with(['#e' => ['1']]);

var_dump($f->toJson());
