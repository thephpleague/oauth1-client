<?php

namespace League\OAuth1\Client\Server;

use InvalidArgumentException;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\SignatureInterface;

class Uservoice extends Server
{
    /** @var string*/
    protected $base;

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

    public function urlTemporaryCredentials(): string
    {
        return $this->base . '/oauth/request_token';
    }

    public function urlAuthorization(): string
    {
        return $this->base . '/oauth/authorize';
    }

    public function urlTokenCredentials(): string
    {
        return $this->base . '/oauth/access_token';
    }

    public function urlUserDetails(): string
    {
        return $this->base . '/api/v1/users/current.json';
    }

    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        $user = new User();

        $user->uid = $data['user']['id'];
        $user->name = $data['user']['name'];
        $user->imageUrl = $data['user']['avatar_url'];
        $user->email = $data['user']['email'];

        if ($data['user']['name']) {
            $parts = explode(' ', $data['user']['name']);

            if (count($parts) > 0) {
                $user->firstName = $parts[0];
            }

            if (count($parts) > 1) {
                $user->lastName = $parts[1];
            }
        }

        $user->urls[] = $data['user']['url'];

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['user']['id'];
    }

    public function userEmail($data, TokenCredentials $tokenCredentials):? string
    {
        return $data['user']['email'];
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials):? string
    {
        return $data['user']['name'];
    }

    /**
     * Parse configuration array to set attributes.
     *
     * @throws InvalidArgumentException If the configuration is missing a host
     */
    private function parseConfigurationArray(array $configuration = []): void
    {
        if (isset($configuration['host'])) {
            throw new InvalidArgumentException('Missing host');
        }

        $this->base = trim($configuration['host'], '/');
    }
}
