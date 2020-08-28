<?php

namespace League\OAuth1\Client\Credentials;

abstract class Credentials implements CredentialsInterface
{
    /** @var string */
    protected $identifier;

    /** @var string */
    protected $secret;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }
}
