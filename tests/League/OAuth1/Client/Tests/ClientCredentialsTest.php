<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\ClientCredentials;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ClientCredentialsTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testManipulating(): void
    {
        $credentials = new ClientCredentials;
        static::assertNull($credentials->getIdentifier());
        $credentials->setIdentifier('foo');
        static::assertEquals('foo', $credentials->getIdentifier());
        static::assertNull($credentials->getSecret());
        $credentials->setSecret('foo');
        static::assertEquals('foo', $credentials->getSecret());
    }
}