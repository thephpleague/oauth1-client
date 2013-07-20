<?php

namespace League\OAuth1\Client\Credentials;

abstract class Credentials implements CredentialsInterface
{
    protected $identifier;

    protected $secret;

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }
}