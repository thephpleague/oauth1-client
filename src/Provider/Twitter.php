<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\User;
use Psr\Http\Message\ResponseInterface;

class Twitter extends BaseProvider
{
    public function extractUserDetails(ResponseInterface $response): User
    {
        $data = json_decode($response->getBody()->getContents(), true);

        $user = (new User())
            ->setId($data['id'])
            ->setUsername($data['screen_name']);

        unset($data['id'], $data['screen_name']);

        if ($data['email'] ?? null) {
            $user->setEmail($data['email']);

            unset($data['email']);
        }

        return $user->setMetadata($data);
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
