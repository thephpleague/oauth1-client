<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\User;
use Psr\Http\Message\ResponseInterface;

class Twitter extends BaseProvider
{
    protected const TEMPORARY_CREDENTIALS_URI = 'https://api.twitter.com/oauth/request_token';
    protected const AUTHORIZATION_URI         = 'https://api.twitter.com/oauth/authenticate';
    protected const TOKEN_CREDENTIALS_URI     = 'https://api.twitter.com/oauth/access_token';
    protected const USER_DETAILS_URI          = 'https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true';

    public function extractUserDetails(ResponseInterface $response): User
    {
        // TODO: Implement extractUserDetails() method.

        return new User();
    }
}