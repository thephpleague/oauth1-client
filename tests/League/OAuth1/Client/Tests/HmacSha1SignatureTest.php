<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Signature\HmacSha1Signature;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HmacSha1SignatureTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testSigningRequest(): void
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

        $uri = 'http://www.example.com/?qux=corge';
        $parameters = ['foo' => 'bar', 'baz' => null];

        static::assertEquals('Ea1S+Lwu/VsY7t3AgduDynFHXDI=', $signature->sign($uri, $parameters));
    }

    public function testSigningRequestWithMultiDimensionalParams(): void
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

        $uri = 'http://www.example.com/';
        $parameters = [
            'a' => [
                'b' => [
                    'c' => 'd',
                ],
                'e' => [
                    'f' => 'g',
                ],
            ],
            'h' => 'i',
            'empty' => '',
            'null' => null,
            'false' => false,
        ];

        static::assertEquals('rx07CShMnywUngphritE0LChIII=', $signature->sign($uri, $parameters));
    }

    protected function getMockClientCredentials()
    {
        $clientCredentials = m::mock(ClientCredentialsInterface::class);
        $clientCredentials->shouldReceive('getSecret')->andReturn('clientsecret');

        return $clientCredentials;
    }
}
