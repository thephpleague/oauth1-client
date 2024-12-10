<?php

namespace League\OAuth1\Client\Tests;

use Generator;
use League\OAuth1\Client\Server\X;
use PHPUnit\Framework\TestCase;

class XServerTest extends TestCase
{
    public function sampleTemporaryCredentialUrls(): Generator
    {
        yield 'No application scope' => [
            null, 'https://api.x.com/oauth/request_token',
        ];

        yield "Read" => [
            'read', 'https://api.x.com/oauth/request_token?x_auth_access_type=read',
        ];

        yield "Write" => [
            'write', 'https://api.x.com/oauth/request_token?x_auth_access_type=write',
        ];
    }

    /** @dataProvider sampleTemporaryCredentialUrls */
    public function testItProvidesNoApplicationScopeByDefault(?string $applicationScope, string $url): void
    {
        $twitter = new X([
            'identifier' => 'mykey',
            'secret' => 'mysecret',
            'callback_uri' => 'http://app.dev/',
            'scope' => $applicationScope,
        ]);

        self::assertEquals($url, $twitter->urlTemporaryCredentials());
    }
}
