<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function it_sets_base_properties(): void
    {
        $user = new User();

        self::assertNull($user->getId());
        self::assertNull($user->getUsername());
        self::assertNull($user->getEmail());
        self::assertEmpty($user->getMetadata());

        $user
            ->setId($id = 123)
            ->setUsername($username = 'bencorlett')
            ->setEmail($email = 'bencorlett@thephpleague.com')
            ->setMetadata($metadata = ['arbitrary' => 'value']);

        self::assertEquals($id, $user->getId());
        self::assertEquals($username, $user->getUsername());
        self::assertEquals($email, $user->getEmail());
        self::assertEquals($metadata, $user->getMetadata());
    }

    /** @test */
    public function it_exposes_metadata_over_array_access(): void
    {
        $user = (new User())->setMetadata([
            'arbitrary' => 'value',
            'nullable'  => null,
        ]);

        self::assertTrue(isset($user['arbitrary']));
        self::assertFalse(isset($user['nullable']));
        self::assertFalse(isset($user['non-existent']));

        self::assertEquals('value', $user['arbitrary']);

        $user['goat'] = 'ben';
        self::assertEquals('ben', $user['goat']);
        unset($user['goat']);
        self::assertFalse(isset($user['goat']));
    }
}
