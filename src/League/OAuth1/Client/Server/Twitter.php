<?php

namespace League\OAuth1\Client\Server;

class Twitter extends IdentityServer
{
    public function urlTemporaryToken()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }
}