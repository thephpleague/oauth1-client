<?php

namespace League\OAuth1\Client\Tests\Provider;

use GuzzleHttp\Psr7\Response;
use Http\Factory\Guzzle\RequestFactory;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Provider\Twitter;
use League\OAuth1\Client\RequestInjector;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use function GuzzleHttp\Psr7\stream_for;

class TwitterProviderTest extends MockeryTestCase
{
    use PreparesRequestInjectorMockInIsolation;

    /** @var ClientCredentials */
    private $clientCredentials;

    /** @var RequestFactory */
    private $requestFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientCredentials = new ClientCredentials(
            '9djdj82h48djs9d2',
            'va90vn89e2pnvp',
            'https://api.client.com/callback'
        );

        $this->requestFactory = new RequestFactory();
    }

    /** @test */
    public function it_creates_the_right_temporary_credentials_request(): void
    {
        $provider = new Twitter($this->clientCredentials);

        $request = $provider->createTemporaryCredentialsRequest($this->requestFactory);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('https://api.twitter.com/oauth/request_token', (string)$request->getUri());
    }

    /** @test */
    public function it_creates_the_right_authorization_request(): void
    {
        $provider = new Twitter($this->clientCredentials);

        $request = $provider->createAuthorizationRequest($this->requestFactory);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('https://api.twitter.com/oauth/authenticate', (string)$request->getUri());
    }

    /** @test */
    public function it_creates_the_right_token_credentials_request(): void
    {
        $provider = new Twitter($this->clientCredentials);

        $request = $provider->createTokenCredentialsRequest($this->requestFactory);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('https://api.twitter.com/oauth/access_token', (string)$request->getUri());
    }

    /** @test */
    public function it_creates_the_right_user_details_request(): void
    {
        $provider = new Twitter($this->clientCredentials);

        $request = $provider->createUserDetailsRequest($this->requestFactory);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals(
            'https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true',
            (string)$request->getUri()
        );
    }

    /** @test */
    public function it_injects_temporary_credentials_parameters_correctly(): void
    {
        $provider = new Twitter($this->clientCredentials);

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_QUERY);

        $request = $provider->createTemporaryCredentialsRequest($this->requestFactory);
        $provider->prepareTemporaryCredentialsRequest($request);

    }

    /** @test */
    public function it_injects_authorization_parameters_correctly(): void
    {
        $provider = new Twitter($this->clientCredentials);

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_QUERY);

        $request = $provider->createAuthorizationRequest($this->requestFactory);

        $provider->prepareAuthorizationRequest(
            $request,
            new Credentials('temporary-id', 'temporary-secret')
        );
    }

    /** @test */
    public function it_injects_token_credentials_parameters_correctly(): void
    {
        $provider = new Twitter($this->clientCredentials);

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_HEADER);

        $request = $provider->createTokenCredentialsRequest($this->requestFactory);

        $provider->prepareTokenCredentialsRequest(
            $request,
            new Credentials('temporary-id', 'temporary-secret'),
            'verifier'
        );
    }

    /** @test */
    public function it_injects_authenticated_request_parameters_correctly(): void
    {
        $provider = new Twitter($this->clientCredentials);

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_HEADER);

        $request = $provider->createUserDetailsRequest($this->requestFactory);

        $provider->prepareAuthenticatedRequest(
            $request,
            new Credentials('token-id', 'token-secret')
        );
    }

    /** @test */
    public function it_can_extract_a_user_without_an_email(): void
    {
        $response = (new Response())->withBody(stream_for(json_encode([
            'id' => $id = 1932485893,
            'screen_name' => $username = 'thephpleague',
            'arbitrary' => 'value',
        ])));

        $user = (new Twitter($this->clientCredentials))->extractUserDetails($response);

        self::assertEquals($id, $user->getId());
        self::assertEquals($username, $user->getUsername());
        self::assertNull($user->getEmail());
        self::assertEquals(['arbitrary' => 'value'], $user->getMetadata());
    }

    /** @test */
    public function it_can_extract_a_user_with_an_email(): void
    {
        $response = (new Response())->withBody(stream_for(json_encode([
            'id' => 1932485893,
            'screen_name' => 'thephpleague',
            'email' => $email = 'bencorlett@thephpleague.com',
        ])));

        $user = (new Twitter($this->clientCredentials))->extractUserDetails($response);

        self::assertEquals($email, $user->getEmail());
    }
}