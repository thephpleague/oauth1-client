<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;

class Twitter extends Server
{
    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials(): string
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization(): string
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials(): string
    {
        return 'https://api.twitter.com/oauth/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails(): string
    {
        return 'https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        $user = new User();

        $user->uid = $data['id_str'];
        $user->nickname = $data['screen_name'];
        $user->name = $data['name'];
        $user->location = $data['location'];
        $user->description = $data['description'];
        $user->imageUrl = $data['profile_image_url'];
        $user->email = null;
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        $used = ['id', 'screen_name', 'name', 'location', 'description', 'profile_image_url', 'email'];

        foreach ($data as $key => $value) {
            if (strpos($key, 'url') !== false) {
                if (!\in_array($key, $used, true)) {
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
        return $data['id'];
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
        return $data['name'];
    }
}
