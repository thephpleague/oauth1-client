<?php

namespace League\OAuth1\Client\Tests\Signature;

use Generator;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use League\OAuth1\Client\Signature\BaseStringBuilder;
use PHPUnit\Framework\TestCase;

class BaseStringBuilderTest extends TestCase
{
    /** @test */
    public function it_can_handle_the_request_from_the_spec_doc(): void
    {
        $request = (new Request('POST', 'http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(stream_for('c2&a3=2+q'));

        $oauthParams = [
            'oauth_consumer_key'     => '9djdj82h48djs9d2',
            'oauth_token'            => 'kkk9d7dh3k39sjv7',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => 137131201,
            'oauth_nonce'            => '7d8f3e4a',
            'oauth_signature'        => 'djosJKDKJSD8743243%2Fjdk33klY%3D'
        ];

        $expected = 'POST&http%3A%2F%2Fexample.com%2Frequest&a2%3Dr%2520b%26a3%3D2%2520q%26a3%3Da%26b5%3D%253D%25253D%26c%2540%3D%26c2%3D%26oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_nonce%3D7d8f3e4a%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D137131201%26oauth_token%3Dkkk9d7dh3k39sjv7';

        self::assertEquals(
            $expected,
            (new BaseStringBuilder())->forRequest($request, $oauthParams),
            'Expected base string from the spec in Section 3.4.1.1. String Construction (Page 19) matches the actual base string'
        );
    }

    public function sampleRequestScenarios(): Generator
    {
        yield 'Basic temporary credentials call with callback in OAuth parameters' => [
            'POST',
            'https://api.example.com/request-temporary-credentials',
            null,
            [
                'oauth_consumer_key'     => '9djdj82h48djs9d2',
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_callback'         => 'https://api.client.com/callback',
            ],
            'POST&https%3A%2F%2Fapi.example.com%2Frequest-temporary-credentials&oauth_callback%3Dhttps%253A%252F%252Fapi.client.com%252Fcallback%26oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_signature_method%3DHMAC-SHA1',
        ];

        yield 'Basic temporary credentials call with callback in body' => [
            'POST',
            'https://api.example.com/request-temporary-credentials',
            ['oauth_callback' => 'https://api.client.com/callback'],
            [
                'oauth_consumer_key'     => '9djdj82h48djs9d2',
                'oauth_signature_method' => 'HMAC-SHA1',
            ],
            'POST&https%3A%2F%2Fapi.example.com%2Frequest-temporary-credentials&oauth_callback%3Dhttps%253A%252F%252Fapi.client.com%252Fcallback%26oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_signature_method%3DHMAC-SHA1',
        ];

        yield 'Basic temporary credentials call with callback in query string' => [
            'POST',
            'https://api.example.com/request-temporary-credentials?oauth_callback=https%3A%2F%2Fapi.client.com%2Fcallback',
            null,
            [
                'oauth_consumer_key'     => '9djdj82h48djs9d2',
                'oauth_signature_method' => 'HMAC-SHA1',
            ],
            'POST&https%3A%2F%2Fapi.example.com%2Frequest-temporary-credentials&oauth_callback%3Dhttps%253A%252F%252Fapi.client.com%252Fcallback%26oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_signature_method%3DHMAC-SHA1',
        ];

        yield 'Realm OAuth parameter is ignored' => [
            'POST',
            'https://api.example.com/request-temporary-credentials',
            null,
            [
                'realm'                  => 'General',
                'oauth_consumer_key'     => '9djdj82h48djs9d2',
                'oauth_signature_method' => 'HMAC-SHA1',
            ],
            'POST&https%3A%2F%2Fapi.example.com%2Frequest-temporary-credentials&oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_signature_method%3DHMAC-SHA1',
        ];

        yield 'Duplicate parameters in differing sources both count' => [
            'POST',
            'https://api.example.com/request-temporary-credentials?foo=bar',
            ['foo' => 'qux'],
            [
                'realm'                  => 'General',
                'oauth_consumer_key'     => '9djdj82h48djs9d2',
                'oauth_signature_method' => 'HMAC-SHA1',
            ],
            'POST&https%3A%2F%2Fapi.example.com%2Frequest-temporary-credentials&foo%3Dbar%26foo%3Dqux%26oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_signature_method%3DHMAC-SHA1',
        ];
    }

    /**
     * @test
     *
     * @dataProvider sampleRequestScenarios
     */
    public function it_can_handle_varying_requests(
        string $method,
        string $uri,
        ?array $body,
        array $oauthParameters,
        string $expected
    ): void {
        $request = new Request($method, $uri);

        if (null !== $body) {
            $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                ->withBody(stream_for(http_build_query($body)));
        }

        self::assertEquals($expected, (new BaseStringBuilder())->forRequest($request, $oauthParameters));
    }
}
