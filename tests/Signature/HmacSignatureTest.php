<?php

namespace League\OAuth1\Client\Tests\Signature;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Signature\HmacSignature;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class HmacSignatureTest extends TestCase
{
    /** @test */
    public function it_signs_a_basic_request()
    {
        $clientCredentials = new ClientCredentials(
            '9djdj82h48djs9d2',
            'clientSecret',
            'https://www.example.com/callback'
        );

        $tokenCredentials = new Credentials(
            'kkk9d7dh3k39sjv7',
            'tokenSecret'
        );

        $signature = HmacSignature::withTokenCredentials($clientCredentials, $tokenCredentials);

        // @link https://tools.ietf.org/html/rfc5849#section-3.4.1.3.1
        $request = (new Request('GET', 'https://api.example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(stream_for('c2&a3=2+q'));

        $signedRequest = $signature->sign($request);

        self::markTestIncomplete('@todo Something with this test…');
    }
}