<?php

namespace League\OAuth1\Client\Token;

interface TokenInterface
{
    public function getToken();

    public function setToken($token);

    public function getSecret();

    public function setSecret($secret);
}