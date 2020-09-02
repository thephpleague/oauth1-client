<?php

namespace League\OAuth1\Client\Credentials;

class Credentials
{
    /** @var string */
    protected $identifier;

    /** @var string */
    protected $secret;

    public function __construct(string $identifier, string $secret)
    {
        $this->identifier = $identifier;
        $this->secret = $secret;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}