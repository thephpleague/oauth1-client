<?php

namespace League\OAuth1\Client\Token;

abstract class AbstractToken implements TokenInterface
{
    protected $token;

    protected $secret;

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
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