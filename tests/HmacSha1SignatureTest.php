<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Signature\HmacSha1Signature;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HmacSha1SignatureTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function testSigningRequest()
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

        $uri = 'http://www.example.com/?qux=corge';
        $parameters = ['foo' => 'bar', 'baz' => null];

        $this->assertEquals('A3Y7C1SUHXR1EBYIUlT3d6QT1cQ=', $signature->sign($uri, $parameters));
    }

    public function testSigningRequestWhereThePortIsNotStandard()
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

        $uri = 'http://www.example.com:8080/?qux=corge';
        $parameters = ['foo' => 'bar', 'baz' => null];

        $this->assertEquals('ECcWxyi5UOC1G0MxH0ygm6Pd6JE=', $signature->sign($uri, $parameters));
    }

    public function testSigningRequestWithMultiDimensionalParams()
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

        $this->assertEquals('ZUxiJKugeEplaZm9e4hshN0I70U=', $signature->sign($uri, $parameters));
    }

    protected function getMockClientCredentials()
    {
        $clientCredentials = m::mock('League\OAuth1\Client\Credentials\ClientCredentialsInterface');
        $clientCredentials->shouldReceive('getSecret')->andReturn('clientsecret');

        return $clientCredentials;
    }
}
