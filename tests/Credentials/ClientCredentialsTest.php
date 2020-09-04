<?php

namespace League\OAuth1\Client\Tests\Credentials;

use League\OAuth1\Client\Credentials\ClientCredentials;
use PHPUnit\Framework\TestCase;

class ClientCredentialsTest extends TestCase
{
    /** @test */
    public function it_returns_a_uri_instance_of_the_given_callback_uri(): void
    {
        $credentials = new ClientCredentials(
            'identifier',
            'secret',
            $callback = 'https://api.client.com/callback'
        );

        self::assertEquals(
            $callback,
            (string) $credentials->getCallbackUri(),
            'The callback URI should not be modified if a string is given'
        );
    }

    /** @test */
    public function it_handles_existent_realms(): void
    {
        self::assertEquals(
            'Photos',
            (new ClientCredentials(
                'identifier',
                'secret',
                'https://api.client.com/callback',
                'Photos'
            ))->getRealm()
        );
    }

    /** @test */
    public function it_handles_non_existent_realms(): void
    {
        self::assertNull(
            (new ClientCredentials(
                'identifier',
                'secret',
                'https://api.client.com/callback'
            ))->getRealm()
        );
    }
}
