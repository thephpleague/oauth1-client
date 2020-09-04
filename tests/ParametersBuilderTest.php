<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\ParametersBuilder;
use League\OAuth1\Client\Signature\Signer;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ParametersBuilderTest extends MockeryTestCase
{
    private const CLIENT_IDENTIFIER = 'client-identifier';
    private const CALLBACK_URI      = 'https://api.client.com/callback';
    private const REALM             = 'Photos';
    private const SIGNATURE_METHOD  = 'HMAC-SHA1';

    /** @var Signer|LegacyMockInterface|MockInterface */
    private $signer;

    /** @var ClientCredentials */
    private $clientCredentials;

    public function setUp(): void
    {
        $this->signer = Mockery::mock(Signer::class);
        $this->signer->shouldReceive('getMethod')->andReturn(self::SIGNATURE_METHOD);

        $this->clientCredentials = new ClientCredentials(
            self::CLIENT_IDENTIFIER,
            'client-secret',
            self::CALLBACK_URI,
            self::REALM
        );
    }

    /** @test */
    public function it_builds_for_temporary_credentials_request(): void
    {
        $builder = new ParametersBuilder($this->signer, self::REALM);

        $expected = [
            'realm'                  => self::REALM,
            'oauth_signature_method' => self::SIGNATURE_METHOD,
            'oauth_consumer_key'     => self::CLIENT_IDENTIFIER,
            'oauth_callback'         => self::CALLBACK_URI,
        ];

        $this->assertParameters($expected, $builder->forTemporaryCredentialsRequest($this->clientCredentials));
    }

    /** @test */
    public function it_builds_for_authorization_request(): void
    {
        $builder = new ParametersBuilder($this->signer, self::REALM);

        $temporaryCredentials = new Credentials('temporary-identifier', 'temporary-secret');

        $expected = [
            'realm'                  => self::REALM,
            'oauth_signature_method' => self::SIGNATURE_METHOD,
            'oauth_token'            => 'temporary-identifier',
        ];

        $this->assertParameters(
            $expected,
            $builder->forAuthorizationRequest($temporaryCredentials)
        );
    }

    /** @test */
    public function it_builds_for_token_credentials_request(): void
    {
        $builder = new ParametersBuilder($this->signer, self::REALM);

        $temporaryCredentials = new Credentials('temporary-identifier', 'temporary-secret');

        $expected = [
            'realm'                  => self::REALM,
            'oauth_signature_method' => self::SIGNATURE_METHOD,
            'oauth_consumer_key'     => self::CLIENT_IDENTIFIER,
            'oauth_token'            => 'temporary-identifier',
            'oauth_verifier'         => 'my-verifier',
        ];

        $this->assertParameters(
            $expected,
            $builder->forTokenCredentialsRequest(
                $this->clientCredentials,
                $temporaryCredentials,
                'my-verifier'
            )
        );
    }

    /** @test */
    public function it_builds_for_authenticated_request(): void
    {
        $builder = new ParametersBuilder($this->signer, self::REALM);

        $tokenCredentials = new Credentials('token-identifier', 'token-secret');

        $expected = [
            'realm'                  => self::REALM,
            'oauth_signature_method' => self::SIGNATURE_METHOD,
            'oauth_consumer_key'     => self::CLIENT_IDENTIFIER,
            'oauth_token'            => 'token-identifier',
        ];

        $this->assertParameters(
            $expected,
            $builder->forAuthenticatedRequest(
                $this->clientCredentials,
                $tokenCredentials
            )
        );
    }

    private function assertParameters(array $expected, array $actual): void
    {
        // Except for the nonce and timestamp, we expect all parameters to be there
        self::assertCount(
            count($expected) + 2,
            $actual,
            'The number of actual parameters lines up with what we expect'
        );

        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $actual);
            self::assertEquals($value, $actual[$key]);
        }

        self::assertArrayHasKey('oauth_nonce', $actual);
        self::assertNotEmpty($actual['oauth_nonce']);

        // Assert the time matches, but just to be sure we don't clock over
        // the change of a second, allow up to a 1 second discrpeency.
        self::assertArrayHasKey('oauth_timestamp', $actual);
        self::assertIsNumeric($actual['oauth_timestamp']);

        self::assertTrue(
            abs(time() - $actual['oauth_timestamp']) < 1,
            'The OAuth timestamp is within 1 second of the current time'
        );
    }
}
