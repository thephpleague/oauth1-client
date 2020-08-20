<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;

class Xing extends Server
{
    private const XING_API_ENDPOINT = 'https://api.xing.com';

    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials(): string
    {
        return self::XING_API_ENDPOINT . '/v1/request_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization(): string
    {
        return self::XING_API_ENDPOINT . '/v1/authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials(): string
    {
        return self::XING_API_ENDPOINT . '/v1/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails(): string
    {
        return self::XING_API_ENDPOINT . '/v1/users/me';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        if (!isset($data['users'][0])) {
            throw new \UnexpectedValueException('Couldn\'t find User data in response.');
        }
        $data = $data['users'][0];

        $user = new User();
        $user->uid = $data['id'];
        $user->nickname = $data['display_name'];
        $user->name = $data['display_name'];
        $user->firstName = $data['first_name'];
        $user->lastName = $data['last_name'];
        $user->location = $data['private_address']['country'];

        if ($user->location === '') {
            $user->location = $data['business_address']['country'];
        }
        $user->description = $data['employment_status'];
        $user->imageUrl = $data['photo_urls']['maxi_thumb'];
        $user->email = $data['active_email'];

        $user->urls['permalink'] = $data['permalink'];

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        $data = $data['users'][0];

        return $data['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials): ?string
    {
        $data = $data['users'][0];

        return $data['active_email'];
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials): ?string
    {
        $data = $data['users'][0];

        return $data['display_name'];
    }
}
