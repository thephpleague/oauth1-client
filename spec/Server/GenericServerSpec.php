<?php

namespace spec\League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\GenericResourceOwner;
use League\OAuth1\Client\Server\GenericServer;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;

class GenericServerSpec extends ObjectBehavior
{
    private $clientIdentifier;

    private $clientSecret;

    private $temporaryCredentialsUrl;

    private $authorizationUrl;

    private $tokenCredentialsUrl;

    private $resourceOwnerDetailsUrl;

    public function let()
    {
        $this->clientIdentifier = 'client_identifier';
        $this->clientSecret = 'client_secret';
        $this->temporaryCredentialsUrl = 'http://server.com/request_token';
        $this->authorizationUrl = 'http://server.com/authorize';
        $this->tokenCredentialsUrl = 'http://server.com/access_token';
        $this->resourceOwnerDetailsUrl = 'http://server.com/me';

        $this->beConstructedWith([
            'identifier' => $this->clientIdentifier,
            'secret' => $this->clientSecret,
            'temporaryCredentialsUrl' => $this->temporaryCredentialsUrl,
            'authorizationUrl' => $this->authorizationUrl,
            'tokenCredentialsUrl' => $this->tokenCredentialsUrl,
            'resourceOwnerDetailsUrl' => $this->resourceOwnerDetailsUrl,
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GenericServer::class);
    }

    public function it_exposes_the_base_temporary_credentials_url()
    {
        $this->getBaseTemporaryCredentialsUrl()->shouldBe($this->temporaryCredentialsUrl);
    }

    public function it_exposes_the_base_authorization_url()
    {
        $this->getBaseAuthorizationUrl()->shouldBe($this->authorizationUrl);
    }

    public function it_exposes_the_base_token_credentials_url()
    {
        $this->getBaseTokenCredentialsUrl()->shouldBe($this->tokenCredentialsUrl);
    }

    public function it_exposes_the_resource_owner_details_url(TokenCredentials $tokenCredentials)
    {
        $this->getResourceOwnerDetailsUrl($tokenCredentials)->shouldBe($this->resourceOwnerDetailsUrl);
    }

    public function it_performs_no_checking_of_a_resource_owner_details_response(ResponseInterface $response)
    {
        $this->checkResourceOwnerDetailsResponse($response, []);
    }

    public function it_creates_a_generic_resource_owner(TokenCredentials $tokenCredentials)
    {
        $this->createResourceOwner(['id' => 123], $tokenCredentials)
            ->shouldBeAnInstanceOf(GenericResourceOwner::class);
    }

    public function it_exposes_client_credentials()
    {
        $clientCredentials = $this->getClientCredentials();

        $clientCredentials->shouldBeAnInstanceOf(ClientCredentials::class);
        $clientCredentials->getIdentifier()->shouldBe($this->clientIdentifier);
        $clientCredentials->getSecret()->shouldBe($this->clientSecret);
    }

    public function it_provides_correct_headers(TokenCredentials $tokenCredentials)
    {
        $tokenCredentials->getIdentifier()->willReturn('token_identifier');
        $tokenCredentials->getSecret()->willReturn('token_secret');

        $headers = $this->getHeaders(
            $tokenCredentials,
            'get',
            $this->tokenCredentialsUrl,
            ['body' => 'parameter']
        );

        $headers->shouldBeArray();
        $headers->shouldHaveCount(1);
        $headers->shouldHaveKey('Authorization');
        $headers['Authorization']->shouldHaveValidOAuthAuthorizationHeader($tokenCredentials);
    }

    public function getMatchers()
    {
        return [
            'haveValidOAuthAuthorizationHeader' => function ($authorizationHeader, Credentials $credentials) {

                // The OAuth protocol specifies a strict number of headers should
                // be sent, in the correct order. We'll validate that here.
                $validAuthorizationHeaderPattern = sprintf(
                    '/^OAuth oauth_consumer_key="%s", oauth_nonce="[a-zA-Z0-9]+", oauth_signature=".*?", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_token="%s", oauth_version="1.0"$/',
                    preg_quote($this->clientIdentifier, '/'),
                    preg_quote($credentials->getIdentifier(), '/')
                );

                return preg_match($validAuthorizationHeaderPattern, $authorizationHeader);
            },
        ];
    }
}
