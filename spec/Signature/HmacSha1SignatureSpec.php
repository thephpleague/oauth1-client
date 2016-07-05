<?php

namespace spec\League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Signature\HmacSha1Signature;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HmacSha1SignatureSpec extends ObjectBehavior
{
    private $uri = 'http://client.com/uri';

    public function let(ClientCredentials $clientCredentials)
    {
        $clientCredentials->getSecret()->willReturn('client_secret');

        $this->beConstructedWith($clientCredentials);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(HmacSha1Signature::class);
    }

    public function it_exposes_a_valid_method()
    {
        $this->getMethod()->shouldBe('HMAC-SHA1');
    }

    public function it_signs_the_request_when_no_additional_credentials_have_been_associated()
    {
        $parameters = ['various' => 'parameters'];

        $this->sign($this->uri, $parameters)->shouldBe('Wr7hY5ETbfForZoOgTLVGqPD+sU=');
    }

    public function it_signs_the_request_when_additional_credentials_have_been_associated(TemporaryCredentials $temporaryCredentials)
    {
        $temporaryCredentials->getSecret()->willReturn('temporary_secret');
        $this->setCredentials($temporaryCredentials);

        $parameters = ['various' => 'parameters'];

        $this->sign($this->uri, $parameters)->shouldBe('7PeCReShibI6HEeQPtqfH6GxiMI=');
    }
}
