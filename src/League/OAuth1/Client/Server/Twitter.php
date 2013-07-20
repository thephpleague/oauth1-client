<?php

namespace League\OAuth1\Client\Server;

class Twitter extends Server
{
    public function urlTemporaryCredentials()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function urlAuthorization()
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    public function urlTokenCredentials()
    {
        return 'https://api.twitter.com/oauth/access_token';
    }
}