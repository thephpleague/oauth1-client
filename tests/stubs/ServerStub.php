<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;

class ServerStub extends Server
{
    /**
     * {@inheritdoc}
     */
    public function urlTemporaryCredentials()
    {
        return 'http://www.example.com/temporary';
    }

    /**
     * {@inheritdoc}
     */
    public function urlAuthorization()
    {
        return 'http://www.example.com/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function urlTokenCredentials()
    {
        return 'http://www.example.com/token';
    }

    /**
     * {@inheritdoc}
     */
    public function urlUserDetails()
    {
        return 'http://www.example.com/user';
    }

    /**
     * {@inheritdoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user = new User;
        $user->firstName = $data['foo'];
        return $user;
    }
}
