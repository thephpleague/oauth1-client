<?php

namespace League\OAuth1\Client\Tests\Signature;

use Generator;
use GuzzleHttp\Psr7\Request;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Signature\BaseStringBuilder;
use League\OAuth1\Client\Signature\HmacSigner;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class HmacSignerTest extends MockeryTestCase
{
    /** @var ClientCredentials */
    private $clientCredentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientCredentials = new ClientCredentials(
            '9djdj82h48djs9d2',
            'va90vn89e2pnvp',
            'https://api.client.com/callback',
            'Photos'
        );
    }

    /** @test */
    public function it_returns_the_correct_oauth_method(): void
    {
        $signer = new HmacSigner($this->clientCredentials);

        self::assertEquals('HMAC-SHA1', $signer->getMethod());
    }

    public function sampleSigningCombinations(): Generator
    {
        yield 'Empty OAuth parameters and client credentials only' => [
            'https://api.example.com/request-temporary-credentials',
            [],
            null,
            '+rbj+zhM7+vt43YBZRwvLu2aLWs='
        ];

        yield 'Empty OAuth parameters with both credentials' => [
            'https://api.example.com/request-temporary-credentials',
            [],
            ['f3928mpf9238nf', 'f8924fj238fr9'],
            'H9BlfyOAIZfx6/9m6W/pNlFGSvk='
        ];

        yield 'OAuth parameters with both credentials' => [
            'https://api.example.com/request-temporary-credentials',
            ['oauth_verifier' => 'pf382j3f2p89'],
            ['f3928mpf9238nf', 'f8924fj238fr9'],
            'f8Apqmdo0GO+DCSJRL/29gBHrGc='
        ];
    }

    /**
     * @test
     *
     * @dataProvider sampleSigningCombinations
     */
    public function it_signs_with_client_credentials_only(string $uri, array $oauthParameters, ?array $contextCredentials, string $expectedSignature): void
    {
        $signer = new HmacSigner(
            $this->clientCredentials,
            $builder = Mockery::mock(BaseStringBuilder::class)
        );

        // This mock just returns a different string based on the given OAuth parameters
        $builder->expects('build')->andReturn($oauthParameters ? json_encode($oauthParameters) : '');

        $request = new Request('GET', $uri);

        self::assertNotEmpty($actualSignature = $signer->sign(
            $request,
            $oauthParameters,
            $contextCredentials ? new Credentials(...$contextCredentials) : null
        ));

        self::assertEquals(
            $expectedSignature,
            $actualSignature,
            'The expected signature is generated given the preconditions passed'
        );
    }
}