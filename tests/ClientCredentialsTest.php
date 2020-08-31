<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\ClientCredentials;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ClientCredentialsTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    /** @test */
    public function manipulation(): void
    {
        $credentials = new ClientCredentials;
        self::assertNull($credentials->getIdentifier());
        $credentials->setIdentifier('foo');
        self::assertEquals('foo', $credentials->getIdentifier());
        self::assertNull($credentials->getSecret());
        $credentials->setSecret('foo');
        self::assertEquals('foo', $credentials->getSecret());
    }
}