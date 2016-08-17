<?php

namespace League\OAuth1\Client\Server;

use Guzzle\Http\Exception\BadResponseException;
use League\OAuth1\Client\Credentials\TokenCredentials;

class Bitbucket extends Server
{
    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials()
    {
        return 'https://bitbucket.org/api/1.0/oauth/request_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization()
    {
        return 'https://bitbucket.org/api/1.0/oauth/authenticate';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials()
    {
        return 'https://bitbucket.org/api/1.0/oauth/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails()
    {
        return 'https://api.bitbucket.org/2.0/user';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user = new User();

        $user->uid = trim($data['uuid'], '{}');
        $user->nickname = $data['username'];
        $user->name = $data['display_name'];
        $user->email = $data['email'];
        $user->urls = $data['links'];
        $user->imageUrl = $data['links']['avatar']['href'];
        $user->location = $data['location'];

        //this is unsafe but its done so we keep BC
        $name_pieces = explode(' ', $data['display_name']);
        $user->firstName = current($name_pieces);
        $user->lastName = last($name_pieces);

        $used = array('username', 'display_name', 'avatar', 'uuid', 'email', 'links', 'location');

        foreach ($data as $key => $value) {
            if (strpos($key, 'url') !== false) {
                if (!in_array($key, $used)) {
                    $used[] = $key;
                }

                $user->urls[$key] = $value;
            }
        }

        // Save all extra data
        $user->extra = array_diff_key($data, array_flip($used));

        return $user;
    }

    protected function fetchUserDetails(TokenCredentials $tokenCredentials, $force = true)
    {
        $user_data = parent::fetchUserDetails($tokenCredentials, $force);
        $this->fetchUserEmail($user_data, $tokenCredentials);
        return $user_data;
    }

    public function fetchUserEmail(&$data, TokenCredentials $tokenCredentials) {
        $email_url = "https://api.bitbucket.org/2.0/user/emails";

        $client  = $this->createHttpClient();
        $headers = $this->getHeaders($tokenCredentials, 'GET', $email_url);

        try {
            $response = $client->get($email_url, $headers)->send();
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $body = $response->getBody();
            $statusCode = $response->getStatusCode();

            throw new \Exception(
                "Received error [$body] with status code [$statusCode] when retrieving token credentials."
            );
        }

        $data['emails'] = $response->json()['values'];
        foreach ($data['emails'] as $details) {
            if ($details['type'] == 'email' && $details['is_confirmed'] && $details['is_primary']) {
                $data['email'] = $details['email'];
                break;
            }
        }

        return $data['email'];
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['username'];
    }

    /**
     * {@inheritDoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return $this->fetchUserEmail($data, $tokenCredentials);
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $data['display_name'];
    }
}
