<?php

namespace League\OAuth1\Client\Credentials;

interface ClientCredentialsInterface extends CredentialsInterface
{
    public function getCallbackUri();

    public function setCallbackUri($callbackUri);
}