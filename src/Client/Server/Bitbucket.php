<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;

class Bitbucket extends Server
{
    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials(): string
    {
        return 'https://bitbucket.org/api/1.0/oauth/request_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization(): string
    {
        return 'https://bitbucket.org/api/1.0/oauth/authenticate';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials(): string
    {
        return 'https://bitbucket.org/api/1.0/oauth/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails(): string
    {
        return 'https://bitbucket.org/api/1.0/user';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        $user = new User();

        $user->uid = $data['user']['username'];
        $user->nickname = $data['user']['username'];
        $user->name = $data['user']['display_name'];
        $user->firstName = $data['user']['first_name'];
        $user->lastName = $data['user']['last_name'];
        $user->imageUrl = $data['user']['avatar'];

        $used = array('username', 'display_name', 'avatar');

        foreach ($data as $key => $value) {
            if (strpos($key, 'url') !== false) {
                if (!in_array($key, $used, true)) {
                    $used[] = $key;
                }

                $user->urls[$key] = $value;
            }
        }

        // Save all extra data
        $user->extra = array_diff_key($data, array_flip($used));

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['user']['username'];
    }

    /**
     * {@inheritDoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials): ?string
    {
        return $data['user']['display_name'];
    }
}
