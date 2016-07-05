<?php

namespace spec\League\OAuth1\Client\Credentials;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Exceptions\CredentialsException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;

class TokenCredentialsSpec extends ObjectBehavior
{
    private $identifier = 'token_identifier';

    private $secret = 'token_secret';

    public function let()
    {
        $this->beConstructedWith(
            $this->identifier,
            $this->secret
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TokenCredentials::class);
    }

    public function it_validates_a_query_string_returns_from_a_response(ResponseInterface $response)
    {
        $response->getBody()->willReturn('==an_invalid_query_string');

        $this->beConstructedThrough('createFromResponse', [$response]);

        $this->shouldThrow(CredentialsException::responseParseError('token'))
            ->duringInstantiation();
    }

    public function it_provides_a_default_error_for_failed_credentials_retrieval(ResponseInterface $response)
    {
        $response->getBody()->willReturn('error=custom_message');

        $this->beConstructedThrough('createFromResponse', [$response]);

        $this->shouldThrow(CredentialsException::tokenCredentialsRetrievalError('custom_message'))
            ->duringInstantiation();
    }

    public function it_can_be_created_from_a_response(ResponseInterface $response)
    {
        $response->getBody()->willReturn(sprintf(
            'oauth_token=%s&oauth_token_secret=%s',
            $this->identifier,
            $this->secret
        ));

        $this->beConstructedThrough('createFromResponse', [$response]);
        $this->shouldHaveType(TokenCredentials::class);
    }

    public function it_returns_the_identifier()
    {
        $this->getIdentifier()->shouldBe($this->identifier);
    }

    public function it_returns_the_secret()
    {
        $this->getSecret()->shouldBe($this->secret);
    }
}
