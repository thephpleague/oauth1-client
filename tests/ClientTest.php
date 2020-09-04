<?php

namespace League\OAuth1\Client\Tests;

use function GuzzleHttp\Psr7\build_query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Http\Factory\Guzzle\RequestFactory;
use League\OAuth1\Client\Client;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Exception\CredentialsFetchingFailedException;
use League\OAuth1\Client\Provider\Provider;
use League\OAuth1\Client\User;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Http\Client\ClientInterface;

class ClientTest extends MockeryTestCase
{
    /** @var Provider|Mockery\LegacyMockInterface|Mockery\MockInterface */
    private $provider;

    /** @var RequestFactory */
    private $requestFactory;

    /** @var ClientInterface|Mockery\LegacyMockInterface|Mockery\MockInterface */
    private $httpClient;

    protected function setUp(): void
    {
        $this->provider = Mockery::mock(Provider::class);

        $this->requestFactory = new RequestFactory();

        $this->httpClient = Mockery::mock(ClientInterface::class);
    }

    /** @test */
    public function it_remembers_temporary_credentials(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        self::assertNull($client->getTemporaryCredentials());
        $client->setTemporaryCredentials(
            $temporaryCredentials = new Credentials('temporary-id', 'temporary-secret')
        );
        self::assertEquals($temporaryCredentials, $client->getTemporaryCredentials());
    }

    /** @test */
    public function it_remembers_the_verifier(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        self::assertNull($client->getVerifier());
        $client->setVerifier('verifier');
        self::assertEquals('verifier', $client->getVerifier());
    }

    /** @test */
    public function it_remembers_token_credentials(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        self::assertNull($client->getTokenCredentials());
        $client->setTokenCredentials(
            $temporaryCredentials = new Credentials('token-id', 'token-secret')
        );
        self::assertEquals($temporaryCredentials, $client->getTokenCredentials());
    }

    private function prepareTemporaryCredentialsRequest(): array
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this
            ->provider
            ->expects('createTemporaryCredentialsRequest')
            ->with($this->requestFactory)
            ->andReturn($request = new Request('GET', 'https://api.example.com/request-temporary-credentials'));

        $this->provider->expects('prepareTemporaryCredentialsRequest')->with($request)->andReturn($request);

        return [$client, $request];
    }

    /** @test */
    public function it_fetches_temporary_credentials_successfully(): void
    {
        /**
         * @var Client  $client
         * @var Request $request
         */
        [$client, $request] = $this->prepareTemporaryCredentialsRequest();

        $body = [
            'oauth_callback_confirmed' => 'true',
            'oauth_token'              => 'temporary-id',
            'oauth_token_secret'       => 'temporary-secret',
        ];

        $this->httpClient->expects('sendRequest')->with($request)->andReturn(
            $response = (new Response())->withBody(stream_for(build_query($body)))
        );

        $credentials = $client->fetchTemporaryCredentials();

        self::assertEquals('temporary-id', $credentials->getIdentifier());
        self::assertEquals('temporary-secret', $credentials->getSecret());

        self::assertEquals(
            $credentials,
            $client->getTemporaryCredentials(),
            'The client remembers temporary credentials after fetching them'
        );
    }

    public function sampleInvalidTemporaryCredentialsResponses(): array
    {
        return [
            [[]],
            [['oauth_token_confirmed' => null]],
            [['oauth_token_confirmed' => '']],
            [['oauth_token_confirmed' => 'false']],
            [['oauth_token_confirmed' => 'true']],
            [['oauth_token_confirmed' => 'true', 'oauth_token' => 'temporary-token']],
            [['oauth_token_confirmed' => 'true', 'oauth_token_secret' => 'temporary-secret']],
        ];
    }

    /**
     * @test
     *
     * @dataProvider sampleInvalidTemporaryCredentialsResponses
     */
    public function it_throws_an_exception_where_the_temporary_credentials_extraction_failed(array $body): void
    {
        /**
         * @var Client  $client
         * @var Request $request
         */
        [$client, $request] = $this->prepareTemporaryCredentialsRequest();

        $this->httpClient->expects('sendRequest')->with($request)->andReturn(
            $response = (new Response())->withBody(stream_for(build_query($body)))
        );

        $this->expectExceptionObject(CredentialsFetchingFailedException::forTemporaryCredentials($response));

        $client->fetchTemporaryCredentials();
    }

    /** @test */
    public function it_prepares_an_authorization_request_successfully(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this
            ->provider
            ->expects('createAuthorizationRequest')
            ->with($this->requestFactory)
            ->andReturn($request = new Request('GET', 'https://api.example.com/authorize'));

        $temporaryCredentials = new Credentials('token-id', 'token-secret');

        $this
            ->provider
            ->expects('prepareAuthorizationRequest')
            ->with($request, $temporaryCredentials)
            ->andReturn($request);

        self::assertEquals($request, $client->prepareAuthorizationRequest($temporaryCredentials));
    }

    /** @test */
    public function it_throw_an_exception_while_preparing_authorization_request_if_temporary_credentials_cannot_be_found(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this->expectException(LogicException::class);

        $client->prepareAuthorizationRequest();
    }

    private function prepareTokenCredentialsRequest(): array
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this
            ->provider
            ->expects('createTokenCredentialsRequest')
            ->with($this->requestFactory)
            ->andReturn($request = new Request('GET', 'https://api.example.com/request-token-credentials'));

        $temporaryCredentials = new Credentials('temporary-identifier', 'temporary-secret');

        $verifier = 'verifier';

        $this
            ->provider
            ->expects('prepareTokenCredentialsRequest')
            ->with($request, $temporaryCredentials, $verifier)
            ->andReturn($request);

        return [
            $client,
            $request,
            $temporaryCredentials,
            $verifier,
        ];
    }

    /** @test */
    public function it_fetches_token_credentials_successfully(): void
    {
        /**
         * @var Client      $client
         * @var Request     $request
         * @var Credentials $temporaryCredentials
         * @var string      $verifier
         */
        [$client, $request, $temporaryCredentials, $verifier] = $this->prepareTokenCredentialsRequest();

        $body = [
            'oauth_token'        => 'token-id',
            'oauth_token_secret' => 'token-secret',
        ];

        $this->httpClient->expects('sendRequest')->with($request)->andReturn(
            $response = (new Response())->withBody(stream_for(build_query($body)))
        );

        $credentials = $client->fetchTokenCredentials($temporaryCredentials, $verifier);

        self::assertEquals('token-id', $credentials->getIdentifier());
        self::assertEquals('token-secret', $credentials->getSecret());

        self::assertEquals(
            $credentials,
            $client->getTokenCredentials(),
            'The client remembers the token credentials after fetching them'
        );
    }

    /** @test */
    public function it_throw_an_exception_while_fetching_token_credentials_if_temporary_credentials_cannot_be_found(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this->expectException(LogicException::class);

        $client->fetchTokenCredentials();
    }

    /** @test */
    public function it_throw_an_exception_while_fetching_token_credentials_if_verifier_cannot_be_found(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $client->setTemporaryCredentials(new Credentials('temporary-id', 'temporary-secret'));

        $this->expectException(LogicException::class);

        $client->fetchTokenCredentials();
    }

    public function sampleInvalidTokenCredentialsResponses(): array
    {
        return [
            [[]],
            [['oauth_token' => null]],
            [['oauth_token' => '']],
            [['oauth_token' => 'false']],
            [['oauth_token' => 'true']],
            [['oauth_token' => 'temporary-token']],
            [['oauth_token_secret' => 'temporary-secret']],
        ];
    }

    /**
     * @test
     *
     * @dataProvider sampleInvalidTokenCredentialsResponses
     */
    public function it_throws_an_exception_where_the_token_credentials_extraction_failed(array $body): void
    {
        /**
         * @var Client      $client
         * @var Request     $request
         * @var Credentials $temporaryCredentials
         * @var string      $verifier
         */
        [$client, $request, $temporaryCredentials, $verifier] = $this->prepareTokenCredentialsRequest();

        $this->httpClient->expects('sendRequest')->with($request)->andReturn(
            $response = (new Response())->withBody(stream_for(build_query($body)))
        );

        $this->expectExceptionObject(CredentialsFetchingFailedException::forTokenCredentials($response));

        $client->fetchTokenCredentials($temporaryCredentials, $verifier);
    }

    /** @test */
    public function it_executes_an_authenticated_request_successfully(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this
            ->provider
            ->expects('prepareAuthenticatedRequest')
            ->with(
                $request = new Request('GET', 'https://api.example.com/me'),
                $tokenCredentials = new Credentials('token-id', 'token-secret')
            )
            ->andReturn($request);

        $this->httpClient->expects('sendRequest')->with($request)->andReturn(
            $response = new Response()
        );

        self::assertEquals(
            $response,
            $client->executeAuthenticatedRequest($request, $tokenCredentials)
        );
    }

    /** @test */
    public function it_throw_an_exception_while_executing_an_authenticated_request_if_token_credentials_cannot_be_found(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this->expectException(LogicException::class);

        $client->executeAuthenticatedRequest(new Request('GET', 'https://api.example.com/me'));
    }

    /** @test */
    public function it_fetches_user_details_by_delegating_to_the_provider(): void
    {
        $client = new Client($this->provider, $this->requestFactory, $this->httpClient);

        $this
            ->provider
            ->expects('createUserDetailsRequest')
            ->with($this->requestFactory)
            ->andReturn($request = new Request('GET', 'https://api.example.com/me'));

        $tokenCredentials = new Credentials('token-id', 'token-secret');

        $this
            ->provider
            ->expects('prepareAuthenticatedRequest')
            ->with($request, $tokenCredentials)
            ->andReturn($request);

        $this->httpClient->expects('sendRequest')->with($request)->andReturn(
            $response = new Response()
        );

        $this
            ->provider
            ->expects('extractUserDetails')
            ->with($response)
            ->andReturn($user = new User());

        self::assertEquals($user, $client->fetchUserDetails($tokenCredentials));
    }
}
