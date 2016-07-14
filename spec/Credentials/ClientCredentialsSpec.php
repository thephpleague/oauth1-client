<?php

namespace spec\League\OAuth1\Client\Credentials;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Exception\ConfigurationException;
use PhpSpec\ObjectBehavior;

class ClientCredentialsSpec extends ObjectBehavior
{
    private $identifier = 'client_identifier';

    private $secret = 'client_secret';

    private $callbackUrl = 'http://client.com/callback';

    public function let()
    {
        $this->beConstructedThrough('createFromOptions', [
            [
                'identifier' => $this->identifier,
                'secret' => $this->secret,
                'callbackUrl' => $this->callbackUrl,
            ],
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ClientCredentials::class);
    }

    public function it_throws_an_exception_when_invalid_options_are_provided()
    {
        $this->beConstructedThrough('createFromOptions', [
            [
                'invalid_configuration',
            ],
        ]);

        $this->shouldThrow(ConfigurationException::class)
            ->duringInstantiation();
    }

    public function it_returns_the_identifier()
    {
        $this->getIdentifier()->shouldBe($this->identifier);
    }

    public function it_returns_the_secret()
    {
        $this->getSecret()->shouldBe($this->secret);
    }

    public function it_returns_the_callback_url()
    {
        $this->getCallbackUrl()->shouldBe($this->callbackUrl);
    }
}
