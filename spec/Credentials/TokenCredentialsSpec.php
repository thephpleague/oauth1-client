<?php

namespace spec\League\OAuth1\Client\Credentials;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Exception\CredentialsException;
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

        $this->shouldThrow(CredentialsException::failedToParseResponse(
                $response->getWrappedObject(),
                'token'
            ))
            ->duringInstantiation();
    }

    public function it_finds_error_in_response_body_for_failed_credentials_parsing(ResponseInterface $response)
    {
        $response->getBody()->willReturn('error=custom_message');

        $this->beConstructedThrough('createFromResponse', [$response]);

        $this->shouldThrow(CredentialsException::failedParsingTokenCredentialsResponse(
                $response->getWrappedObject(),
                'custom_message'
            ))
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
