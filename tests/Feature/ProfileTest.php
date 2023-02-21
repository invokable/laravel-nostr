<?php

declare(strict_types=1);

namespace Tests\Feature;

use Revolution\Nostr\Profile;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_name()
    {
        $p = new Profile(
            name: 'test',
            display_name: '',
            about: '',
            picture: '',
            banner: '',
            website: '',
            nip05: '',
            lud06: '',
            lud16: '',
        );

        $p->display_name = 'display_name';

        $this->assertSame('test', $p->name);
        $this->assertSame('display_name', $p->display_name);
    }

    public function test_toarray()
    {
        $p = new Profile(
            name: 'test',
        );

        $this->assertSame([
            'name' => 'test',
            'display_name' => '',
            'about' => '',
            'picture' => '',
            'banner' => '',
            'website' => '',
            'nip05' => '',
            'lud06' => '',
            'lud16' => '',
        ], $p->toArray());
    }

    public function test_tojson()
    {
        $p = new Profile(
            name: 'test',
        );

        $this->assertSame(json_encode([
            'name' => 'test',
            'display_name' => '',
            'about' => '',
            'picture' => '',
            'banner' => '',
            'website' => '',
            'nip05' => '',
            'lud06' => '',
            'lud16' => '',
        ]), $p->toJson());
    }

    public function test_tostring()
    {
        $p = new Profile(
            name: 'test',
        );

        $this->assertSame(json_encode([
            'name' => 'test',
            'display_name' => '',
            'about' => '',
            'picture' => '',
            'banner' => '',
            'website' => '',
            'nip05' => '',
            'lud06' => '',
            'lud16' => '',
        ]), (string) $p);
    }

    public function test_from_json()
    {
        $p = Profile::fromJson(json_encode([
            'name' => 'test',
        ]));

        $this->assertSame([
            'name' => 'test',
            'display_name' => '',
            'about' => '',
            'picture' => '',
            'banner' => '',
            'website' => '',
            'nip05' => '',
            'lud06' => '',
            'lud16' => '',
        ], $p->toArray());
    }

    public function test_from_array()
    {
        $p = Profile::fromArray([
            'name' => 'test',
        ]);

        $this->assertSame([
            'name' => 'test',
            'display_name' => '',
            'about' => '',
            'picture' => '',
            'banner' => '',
            'website' => '',
            'nip05' => '',
            'lud06' => '',
            'lud16' => '',
        ], $p->toArray());
    }
}
