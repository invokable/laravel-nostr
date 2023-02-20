<?php

declare(strict_types=1);

namespace Tests\Feature;

use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Tests\TestCase;

class FilterTest extends TestCase
{
    public function test_filter()
    {
        $f = new Filter(
            ids: ['a'],
            authors: ['1'],
            kinds: [Kind::Metadata->value, 1],
            since: 0,
            until: 0,
            limit: 0,
        );

        $f->with(['#e' => ['1']]);

        $this->assertSame(json_encode([
            'ids' => ['a'],
            'authors' => ['1'],
            'kinds' => [0, 1],
            'since' => 0,
            'until' => 0,
            'limit' => 0,
            '#e' => ['1'],
        ]), (string) $f);
    }

    public function test_filters_to_array()
    {
        $filters = [
            new Filter(authors: ['1']),
            new Filter(authors: ['2']),
        ];

        $f = collect($filters)->toArray();

        $this->assertSame([['authors' => ['1']], ['authors' => ['2']]], $f);
    }
}
