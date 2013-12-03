<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;

class Bitbucket extends Server
{
    /**
    * {@inheritdoc}
    */
    public function urlTemporaryCredentials()
    {
        return 'https://bitbucket.org/api/1.0/oauth/request_token';
    }

    /**
    * {@inheritdoc}
    */
    public function urlAuthorization()
    {
        return 'https://bitbucket.org/api/1.0/oauth/authenticate';
    }

    /**
    * {@inheritdoc}
    */
    public function urlTokenCredentials()
    {
        return 'https://bitbucket.org/api/1.0/oauth/access_token';
    }

    /**
    * {@inheritdoc}
    */
    public function urlUserDetails()
    {
        return 'https://bitbucket.org/api/1.0/user';
    }

    /**
    * {@inheritdoc}
    */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user = new User;

        $user->nickname = $data['user']['username'];
        $user->name = $data['user']['display_name'];
        $user->imageUrl = $data['user']['avatar'];

        $used = array('username', 'display_name', 'avatar');

        foreach ($data as $key => $value) {
            if (strpos($key, 'url') !== false) {

                if ( ! in_array($key, $used)) {
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
    * {@inheritdoc}
    */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['user']['username'];
    }

    /**
    * {@inheritdoc}
    */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return;
    }

    /**
    * {@inheritdoc}
    */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $data['user']['display_name'];
    }
}
