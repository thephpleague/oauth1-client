<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;
use League\OAuth1\Client\Signature\PlainTextSignature;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PlainTextSignatureTest extends TestCase
{
    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown(): void
    {
        m::close();
    }

    public function testSigningRequest(): void
    {
        $signature = new PlainTextSignature($this->getMockClientCredentials());
        static::assertEquals('clientsecret&', $signature->sign($uri = 'http://www.example.com/'));

        $signature->setCredentials($this->getMockCredentials());
        static::assertEquals('clientsecret&tokensecret', $signature->sign($uri));
        static::assertEquals('PLAINTEXT', $signature->method());
    }

    protected function getMockClientCredentials()
    {
        $clientCredentials = m::mock(ClientCredentialsInterface::class);
        $clientCredentials->shouldReceive('getSecret')->andReturn('clientsecret');

        return $clientCredentials;
    }

    protected function getMockCredentials()
    {
        $credentials = m::mock(CredentialsInterface::class);
        $credentials->shouldReceive('getSecret')->andReturn('tokensecret');

        return $credentials;
    }
}
