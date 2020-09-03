<?php

namespace League\OAuth1\Client\Tests\Provider;

use GuzzleHttp\Psr7\Response;
use Http\Factory\Guzzle\RequestFactory;
use InvalidArgumentException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\ParametersBuilder;
use League\OAuth1\Client\Provider\GenericProvider;
use League\OAuth1\Client\RequestInjector;
use League\OAuth1\Client\Signature\HmacSigner;
use League\OAuth1\Client\Signature\Signer;
use League\OAuth1\Client\User;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class GenericProviderTest extends MockeryTestCase
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
            'https://api.client.com/callback',
            'Photos'
        );

        $this->requestFactory = new RequestFactory();
    }

    private function getFullConfig(): array
    {
        return [
            'temporary_credentials' => [
                'method'   => 'POST',
                'location' => RequestInjector::LOCATION_HEADER,
                'uri'      => 'https://api.example.com/request-temporary-credentials',
            ],
            'authorization' => [
                'method'   => 'POST',
                'location' => RequestInjector::LOCATION_HEADER,
                'uri'      => 'https://api.example.com/authorize',
            ],
            'token_credentials' => [
                'method'   => 'GET',
                'location' => RequestInjector::LOCATION_QUERY,
                'uri'      => 'https://api.example.com/request-token-credentials',
            ],
            'user_details' => [
                'method' => 'POST',
                'uri'    => 'https://api.example.com/me',
            ],
            'authenticated' => [
                'location' => RequestInjector::LOCATION_BODY,
            ],
        ];
    }

    /** @test */
    public function it_respects_config_when_creating_requests(): void
    {
        $provider = new GenericProvider($this->clientCredentials, $config = $this->getFullConfig());

        $requests = [
            'temporary_credentials' => $provider->createTemporaryCredentialsRequest($this->requestFactory),
            'authorization'         => $provider->createAuthorizationRequest($this->requestFactory),
            'token_credentials'     => $provider->createTokenCredentialsRequest($this->requestFactory),
            'user_details'          => $provider->createUserDetailsRequest($this->requestFactory),
        ];

        foreach ($requests as $type => $request) {
            self::assertEquals($config[$type]['method'], $request->getMethod());
            self::assertEquals($config[$type]['uri'], (string) $request->getUri());
        }
    }

    /**
     * @test
     *
     * @depends it_respects_config_when_creating_requests
     */
    public function it_injects_temporary_credentials_parameters_correctly(): void
    {
        $provider = new GenericProvider($this->clientCredentials, $config = $this->getFullConfig());

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_HEADER);

        $request = $provider->createTemporaryCredentialsRequest($this->requestFactory);
        $provider->prepareTemporaryCredentialsRequest($request);
    }

    /**
     * @test
     *
     * @depends it_respects_config_when_creating_requests
     */
    public function it_injects_authorization_parameters_correctly(): void
    {
        $provider = new GenericProvider($this->clientCredentials, $config = $this->getFullConfig());

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_HEADER);

        $request = $provider->createAuthorizationRequest($this->requestFactory);

        $provider->prepareAuthorizationRequest(
            $request,
            new Credentials('temporary-id', 'temporary-secret')
        );
    }

    /**
     * @test
     *
     * @depends it_respects_config_when_creating_requests
     */
    public function it_injects_token_credentials_parameters_correctly(): void
    {
        $provider = new GenericProvider($this->clientCredentials, $config = $this->getFullConfig());

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_QUERY);

        $request = $provider->createTokenCredentialsRequest($this->requestFactory);

        $provider->prepareTokenCredentialsRequest(
            $request,
            new Credentials('temporary-id', 'temporary-secret'),
            'verifier'
        );
    }

    /**
     * @test
     *
     * @depends it_respects_config_when_creating_requests
     */
    public function it_injects_authenticated_request_parameters_correctly(): void
    {
        $provider = new GenericProvider($this->clientCredentials, $config = $this->getFullConfig());

        /** @var RequestInjector|Mockery\MockInterface $requestInjector */
        $requestInjector = $this->prepareRequestInjectorMockInIsolation($provider);

        $requestInjector->expects('inject')->withSomeOfArgs(RequestInjector::LOCATION_BODY);

        $request = $provider->createUserDetailsRequest($this->requestFactory);

        $provider->prepareAuthenticatedRequest(
            $request,
            new Credentials('token-id', 'token-secret')
        );
    }

    public function sampleConfigWithInvalidUris(): array
    {
        return [
            [['temporary_credentials' => ['uri' => '']], 'createTemporaryCredentialsRequest'],
            [['temporary_credentials' => ['uri' => 'not a URI']], 'createTemporaryCredentialsRequest'],
            [['authorization' => ['uri' => '']], 'createAuthorizationRequest'],
            [['authorization' => ['uri' => 'not a URI']], 'createAuthorizationRequest'],
            [['token_credentials' => ['uri' => '']], 'createTokenCredentialsRequest'],
            [['token_credentials' => ['uri' => 'not a URI']], 'createTokenCredentialsRequest'],
            [['user_details' => ['uri' => '']], 'createUserDetailsRequest'],
            [['user_details' => ['uri' => 'not a URI']], 'createUserDetailsRequest'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider sampleConfigWithInvalidUris
     */
    public function it_throws_an_exception_when_invalid_uris_are_configured(array $config, string $method): void
    {
        $provider = new GenericProvider($this->clientCredentials, $config);

        $this->expectException(LogicException::class);

        $provider->{$method}($this->requestFactory);
    }

    /** @test */
    public function it_extracts_user_details(): void
    {
        $provider = new GenericProvider($this->clientCredentials);

        $user = new User();

        $provider->extractUserDetailsUsing(static function () use ($user): User {
            return $user;
        });

        self::assertEquals($user, $provider->extractUserDetails(new Response()));
    }

    /** @test */
    public function it_requires_a_user_details_extractor_before_extracting_user_details(): void
    {
        $provider = new GenericProvider($this->clientCredentials);

        $this->expectException(LogicException::class);

        $provider->extractUserDetails(new Response());
    }

    /** @test */
    public function it_requires_the_user_extractor_returns_a_user(): void
    {
        $provider = new GenericProvider($this->clientCredentials);

        $provider->extractUserDetailsUsing(static function (): void {
            //
        });

        $this->expectException(InvalidArgumentException::class);

        $provider->extractUserDetails(new Response());
    }

    /** @test */
    public function it_creates_a_hmac_signer_by_default(): void
    {
        $provider = new GenericProvider($this->clientCredentials);

        self::assertInstanceOf(HmacSigner::class, $provider->getSigner());
    }

    /** @test */
    public function it_can_create_a_parameters_builder_without_error(): void
    {
        $provider = new GenericProvider($this->clientCredentials);

        $provider->getParametersBuilder();

        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_can_create_a_request_injector_without_error(): void
    {
        $provider = new GenericProvider($this->clientCredentials);

        $provider->getRequestInjector();

        $this->addToAssertionCount(1);
    }
}
