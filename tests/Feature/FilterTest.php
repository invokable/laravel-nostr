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
            kinds: [Kind::Metadata, 1],
            since: 0,
            until: 0,
            limit: 0,
            search: 'test',
        );

        $f->with(['#e' => ['1']]);

        $this->assertSame(json_encode([
            'ids' => ['a'],
            'authors' => ['1'],
            'kinds' => [0, 1],
            'since' => 0,
            'until' => 0,
            'limit' => 0,
            'search' => 'test',
            '#e' => ['1'],
        ]), (string) $f);
    }

    public function test_filter_make()
    {
        $f = Filter::make(
            ids: ['a'],
            authors: ['1'],
            kinds: [Kind::Metadata, 1],
            since: 0,
            until: 0,
            limit: 0,
        )->with(['#e' => ['1']]);

        $this->assertSame(json_encode([
            'ids' => ['a'],
            'authors' => ['1'],
            'kinds' => [0, 1],
            'since' => 0,
            'until' => 0,
            'limit' => 0,
            '#e' => ['1'],
        ]), $f->toJson());
    }

    public function test_filters_to_array()
    {
        $filters = [
            new Filter(authors: ['1'], kinds: [Kind::Metadata]),
            new Filter(authors: ['2']),
        ];

        $f = collect($filters)->toArray();

        $this->assertSame([['authors' => ['1'], 'kinds' => [0]], ['authors' => ['2']]], $f);
    }

    public function test_filter_array_shapes()
    {
        $f = Filter::make(
            kinds: [Kind::Metadata, 1],
        )->with(['#e' => ['test']]);

        $this->assertSame(Kind::Metadata->value, $f->toArray()['kinds'][0]);
        $this->assertSame(['test'], $f->toArray()['#e']);
    }
}
