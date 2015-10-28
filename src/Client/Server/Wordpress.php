<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;

/**
 * WordPress OAuth 1.0a.
 *
 */
class Wordpress extends Server
{
    /**
     * Base uri.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * {@inheritDoc}
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        parent::__construct($clientCredentials, $signature);

        if (is_array($clientCredentials)) {
            $this->parseConfigurationArray($clientCredentials);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials()
    {
        return $this->baseUri . '/oauth1/request';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization()
    {
        return $this->baseUri . '/oauth1/authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials()
    {
        return $this->baseUri . '/oauth1/token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails()
    {
        return $this->baseUri . '/wp-json/wp/v2/users/me';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user = new User();
        
        $user->uid = $data['id'];
        $user->name = $data['name'];
        $user->description = $data['description'];
        $user->email = $data['email'];
        $user->username = $data['username'];

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
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return $data['email'];
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $data['username'];
    }

    /**
     * Parse configuration array to set attributes.
     *
     * @param array $configuration
     * @throws \Exception
     */
    private function parseConfigurationArray(array $configuration = array())
    {
        if (!isset($configuration['host'])) {
            throw new \Exception('Missing WordPress Host');
        }
        $url = parse_url($configuration['host']);
        $this->baseUri = sprintf('%s://%s', $url['scheme'], $url['host']);

        if (isset($url['port'])) {
            $this->baseUri .= ':'.$url['port'];
        }

        if (isset($url['path'])) {
            $this->baseUri .= '/'.trim($url['path'], '/');
        }
    }
}
