<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;
use LogicException;

class Tumblr extends Server
{
    public function urlTemporaryCredentials(): string
    {
        return 'https://www.tumblr.com/oauth/request_token';
    }

    public function urlAuthorization(): string
    {
        return 'https://www.tumblr.com/oauth/authorize';
    }

    public function urlTokenCredentials(): string
    {
        return 'https://www.tumblr.com/oauth/access_token';
    }

    public function urlUserDetails(): string
    {
        return 'https://api.tumblr.com/v2/user/info';
    }

    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        // If the API has broke, return nothing
        if ( ! is_array($data['response']['user'] ?? null)) {
            throw new LogicException('Not possible to get user info');
        }

        $data = $data['response']['user'];

        $user = new User();

        $user->nickname = $data['name'];

        // Save all extra data
        $used = ['name'];
        $user->extra = array_diff_key($data, array_flip($used));

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        if ( ! is_array($data['response']['user'] ?? null)) {
            return null;
        }

        $data = $data['response']['user'];

        return $data['name'];
    }

    public function userEmail($data, TokenCredentials $tokenCredentials):? string
    {
        return null;
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials):? string
    {
        if ( ! is_array($data['response']['user'] ?? null)) {
            return null;
        }

        $data = $data['response']['user'];

        return $data['name'];
    }
}
