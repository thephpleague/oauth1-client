<?php

namespace League\OAuth1\Client\Server;

interface ServerInterface
{
    public function getClientId();

    public function getClientSecret();

    public function getCallbackUri();
}