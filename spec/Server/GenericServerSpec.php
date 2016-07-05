<?php

namespace spec\League\OAuth1\Client\Server;

use League\OAuth1\Client\Server\GenericServer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
}
