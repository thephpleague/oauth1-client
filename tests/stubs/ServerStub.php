<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;

class ServerStub extends Server
{
    public function urlTemporaryCredentials(): string
    {
        return 'http://www.example.com/temporary';
    }

    public function urlAuthorization(): string
    {
        return 'http://www.example.com/authorize';
    }

    public function urlTokenCredentials(): string
    {
        return 'http://www.example.com/token';
    }

    public function urlUserDetails(): string
    {
        return 'http://www.example.com/user';
    }

    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        $user = new User;
        $user->firstName = $data['foo'];
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['id'] ?? null;
    }

    public function userEmail($data, TokenCredentials $tokenCredentials):? string
    {
        return $data['contact_email'] ?? null;
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials):? string
    {
        return $data['username'] ?? null;
    }
}
