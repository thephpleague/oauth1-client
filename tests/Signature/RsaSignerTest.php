<?php

namespace League\OAuth1\Client\Tests\Signature;

use Generator;
use GuzzleHttp\Psr7\Request;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Credentials\RsaKeyPair;
use League\OAuth1\Client\Signature\BaseStringBuilder;
use League\OAuth1\Client\Signature\HmacSigner;
use League\OAuth1\Client\Signature\RsaSigner;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RsaSignerTest extends MockeryTestCase
{
    /** @var ClientCredentials */
    private $clientCredentials;

    /** @var RsaKeyPair */
    private $keyPair;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientCredentials = new ClientCredentials(
            '9djdj82h48djs9d2',
            'va90vn89e2pnvp',
            'https://api.client.com/callback',
            'Photos'
        );

        $this->keyPair = new RsaKeyPair(
            __DIR__ . '/../files/public.pem',
            __DIR__ . '/../files/private.pem',
            'a-passphrase'
        );
    }

    /** @test */
    public function it_returns_the_correct_oauth_method(): void
    {
        $signer = new RsaSigner($this->clientCredentials, $this->keyPair);

        self::assertEquals('RSA-SHA1', $signer->getMethod());
    }

    public function sampleSigningCombinations(): Generator
    {
        yield 'Empty OAuth parameters and client credentials only' => [
            'https://api.example.com/request-temporary-credentials',
            [],
            null,
            'F+EPpW9P8ITMw62MA3KTRkx50/js9Hzrfze8u+ZQIEAq18YW685eJhn2+X2zB6MU7KLMjBQDJ9+g1xKCuX9hk+mQgMjVgpe9wKPG2873jOr6dCWBcvIlJIGBOtWhPYHO7czTREUnJrnOmu5ATdou+tbzHMOS8itwt9XeVGycZwE1jXoxfr099vwfvri8FpdXGGv/bUCHeL7sAwXd59KO0dDqH+8TQ5NMa8ICREmBE9rG29iBcLRfcdR14yZ4iA90N1/Dukc+hkRqntT8B7b1rOMgiXRkKt7YlkgRyDn5GYpha3HXmqkAqryricMlrbus07gEngLWU08nFuiKtozgsw==',
        ];

        yield 'Empty OAuth parameters with both credentials' => [
            'https://api.example.com/request-temporary-credentials',
            [],
            ['f3928mpf9238nf', 'f8924fj238fr9'],
            'F+EPpW9P8ITMw62MA3KTRkx50/js9Hzrfze8u+ZQIEAq18YW685eJhn2+X2zB6MU7KLMjBQDJ9+g1xKCuX9hk+mQgMjVgpe9wKPG2873jOr6dCWBcvIlJIGBOtWhPYHO7czTREUnJrnOmu5ATdou+tbzHMOS8itwt9XeVGycZwE1jXoxfr099vwfvri8FpdXGGv/bUCHeL7sAwXd59KO0dDqH+8TQ5NMa8ICREmBE9rG29iBcLRfcdR14yZ4iA90N1/Dukc+hkRqntT8B7b1rOMgiXRkKt7YlkgRyDn5GYpha3HXmqkAqryricMlrbus07gEngLWU08nFuiKtozgsw==',
        ];

        yield 'OAuth parameters with both credentials' => [
            'https://api.example.com/request-temporary-credentials',
            ['oauth_verifier' => 'pf382j3f2p89'],
            ['f3928mpf9238nf', 'f8924fj238fr9'],
            'D5fS/KqSPIooVB11st15+yDiTJtZeluHf9cB43l9MU8rbwuBUqmI5efspHObO9hgLRN70fyb4wfmUONjyY6H2LEgXq5FRLy2WjR2/cG97qJaz4zpPxKahXkZVin5xPKZlLgjCV3Oj9c90fhtOSvVR3JYxbcVRgA8+nJDSpljRH4ZYQALgADdFcKoD9DojZmnBnVIE7rRBkH+qf00R6is+7EBjjbqTF7WInaAUBCnOEnTJmNnM8TRfwm9KJQu/1jprK1bJrFZWtS+iJ4/7uGDmhRgw1+a02XedylUzHQ08MQFHOHUgMGe3GUuxHXUSEBnx9+RbDuBnqdFGg5cYJVS6Q==',
        ];
    }

    /**
     * @test
     *
     * @dataProvider sampleSigningCombinations
     */
    public function it_signs_correctly_with_varying_combinations(
        string $uri,
        array $oauthParameters,
        ?array $contextCredentials,
        string $expectedSignature
    ): void {
        $signer = new RsaSigner(
            $this->clientCredentials,
            $this->keyPair,
            $builder = Mockery::mock(BaseStringBuilder::class)
        );

        $builder->expects('forRequest')->andReturn($oauthParameters ? json_encode($oauthParameters) : '');

        $request = new Request('GET', $uri);

        self::assertNotEmpty($actualSignature = $signer->sign(
            $request,
            $oauthParameters,
            $contextCredentials ? new Credentials(...$contextCredentials) : null
        ));

        self::assertEquals(
            $expectedSignature,
            $actualSignature,
            'The expected RSA-SHA1 signature is generated given the preconditions passed'
        );
    }
}
