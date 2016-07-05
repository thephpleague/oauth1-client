<?php

namespace spec\League\OAuth1\Client\Credentials;

use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Exceptions\ConfigurationException;
use League\OAuth1\Client\Exceptions\CredentialsException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

class TemporaryCredentialsSpec extends ObjectBehavior
{
    private $identifier = 'temporary_identifier';

    private $secret = 'temporary_secret';

    public function let()
    {
        $this->beConstructedWith(
            $this->identifier,
            $this->secret
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TemporaryCredentials::class);
    }

    public function it_validates_a_query_string_returns_from_a_response(ResponseInterface $response)
    {
        $response->getBody()->willReturn('==an_invalid_query_string');

        $this->beConstructedThrough('createFromResponse', [$response]);

        $this->shouldThrow(CredentialsException::responseParseError('temporary'))
            ->duringInstantiation();
    }

    public function it_provides_a_default_error_for_failed_credentials_retrieval(ResponseInterface $response)
    {
        $response->getBody()->willReturn('oauth_callback_confirmed=false');

        $this->beConstructedThrough('createFromResponse', [$response]);

        $this->shouldThrow(CredentialsException::temporaryCredentialsRetrievalError())
            ->duringInstantiation();
    }

    public function it_relays_custom_error_message_for_failed_credentials_retrieval(ResponseInterface $response)
    {
        $response->getBody()->willReturn('oauth_callback_confirmed=false&error=custom_message');

        $this->beConstructedThrough('createFromResponse', [$response]);

        $this->shouldThrow(CredentialsException::temporaryCredentialsRetrievalError('custom_message'))
            ->duringInstantiation();
    }

    public function it_can_be_created_from_a_response(ResponseInterface $response)
    {
        $response->getBody()->willReturn(sprintf(
            'oauth_callback_confirmed=true&oauth_token=%s&oauth_token_secret=%s',
            $this->identifier,
            $this->secret
        ));

        $this->beConstructedThrough('createFromResponse', [$response]);
        $this->shouldHaveType(TemporaryCredentials::class);
    }

    public function it_returns_the_identifier()
    {
        $this->getIdentifier()->shouldBe($this->identifier);
    }

    public function it_returns_the_secret()
    {
        $this->getSecret()->shouldBe($this->secret);
    }

    public function it_checks_a_valid_identifier_and_no_exception_is_thrown()
    {
        $this->checkIdentifier($this->identifier);
    }

    public function it_throws_an_exception_when_an_invalid_identifier_is_passed()
    {
        $this->shouldThrow(ConfigurationException::class)
            ->duringCheckIdentifier('invalid_temporary_identifier');
    }
}
