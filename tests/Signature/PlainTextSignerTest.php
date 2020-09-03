<?php

namespace League\OAuth1\Client\Tests\Signature;

use GuzzleHttp\Psr7\Request;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Signature\PlainTextSigner;
use PHPStan\Testing\TestCase;

class PlainTextSignerTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_oauth_method(): void
    {
        $clientCredentials = new ClientCredentials(
            'identifier',
            'secret',
            'https://api.client.com/callback'
        );

        self::assertEquals(
            'PLAINTEXT',
            (new PlainTextSigner($clientCredentials))->getMethod()
        );
    }

    public function sampleCredentialsAndSignatures(): array
    {
        return [
            ['abc', null, 'abc&'],
            ['abc', 'def', 'abc&def'],
            ['%a b @c', '&@#!ddf', '%25a%20b%20%40c&%26%40%23%21ddf'],
            ['F#Ff32j8%@', null, 'F%23Ff32j8%25%40&'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider sampleCredentialsAndSignatures
     */
    public function it_signs_correctly(string $clientSecret, ?string $tokenSecret, string $signature): void
    {
        $clientCredentials = new ClientCredentials(
            'identifier',
            $clientSecret,
            'https://api.client.com/callback'
        );

        if (null !== $tokenSecret) {
            $tokenCredentials = new Credentials('token-identifier', $tokenSecret);
        }

        $signer = new PlainTextSigner($clientCredentials);

        self::assertEquals($signature, $signer->sign(
            new Request('GET', 'https://api.example.com/me'),
            [],
            $tokenCredentials ?? null
        ));
    }
}
