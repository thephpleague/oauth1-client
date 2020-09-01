<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\User;
use Psr\Http\Message\ResponseInterface;

class Twitter extends BaseProvider
{
    public function extractUserDetails(ResponseInterface $response): User
    {
        // TODO: Implement extractUserDetails() method.

        return new User();
    }

    protected function getTemporaryCredentialsUri(): string
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    protected function getAuthorizationUri(): string
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    protected function getTokenCredentialsUri(): string
    {
        return 'https://api.twitter.com/oauth/access_token';
    }

    protected function getUserDetailsUri(): string
    {
        return 'https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true';
    }
}