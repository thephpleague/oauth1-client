<?php

namespace League\OAuth1\Client\Tests\Signature;

use GuzzleHttp\Psr7\Request;
use League\OAuth1\Client\Signature\ParameterNormalizer;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ParameterNormalizerTest extends TestCase
{
    /** @test */
    public function it_can_normalize_the_request_from_the_spec_doc()
    {
        $request = (new Request('GET', 'https://api.example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(stream_for('c2&a3=2+q'));

        $oauthParams = [
            'oauth_consumer_key' => '9djdj82h48djs9d2',
            'oauth_token' => 'kkk9d7dh3k39sjv7',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '137131201',
            'oauth_nonce' => '7d8f3e4a',
            'oauth_signature' => 'djosJKDKJSD8743243%2Fjdk33klY%3D'
        ];

        $expected = 'a2=r%20b&a3=2%20q&a3=a&b5=%3D%253D&c%40=&c2=&oauth_consumer_key=9djdj82h48djs9d2&oauth_nonce=7d8f3e4a&oauth_signature_method=HMAC-SHA1&oauth_timestamp=137131201&oauth_token=kkk9d7dh3k39sjv7';

        self::assertEquals(
            $expected,
            (new ParameterNormalizer())->extractAndNormalize($request, $oauthParams),
            'The normalized parameters for the sample request in 3.4.1.3.1. Parameter Sources (Page 22) match the concatenated pairs in 3.4.1.3.2. Parameters Normalization on (Page 24)'
        );
    }
}