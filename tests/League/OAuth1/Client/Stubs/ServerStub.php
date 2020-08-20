<?php

namespace League\OAuth1\Client\Stubs;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;

class ServerStub extends Server
{
    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials(): string
    {
        return 'http://www.example.com/temporary';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization(): string
    {
        return 'http://www.example.com/authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials(): string
    {
        return 'http://www.example.com/token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails(): string
    {
        return 'http://www.example.com/user';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        $user = new User;
        $user->firstName = $data['foo'];

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['id'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials): ?string
    {
        return $data['contact_email'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials): ?string
    {
        return $data['username'] ?? null;
    }
}
