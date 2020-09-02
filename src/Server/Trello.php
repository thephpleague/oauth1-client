<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\SignatureInterface;

class Trello extends Server
{
    /** @var string */
    protected $accessToken;

    /** @var string */
    protected $applicationExpiration;

    /** @var string */
    protected $applicationKey;

    /** @var string */
    protected $applicationName;

    /** @var string */
    protected $applicationScope;

    /**
     * {@inheritDoc}
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        parent::__construct($clientCredentials, $signature);

        if (is_array($clientCredentials)) {
            $this->parseConfiguration($clientCredentials);
        }
    }

    /**
     * Set the access token.
     */
    public function setAccessToken(string $accessToken): Trello
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set the application expiration.
     */
    public function setApplicationExpiration(string $applicationExpiration): Trello
    {
        $this->applicationExpiration = $applicationExpiration;

        return $this;
    }

    /**
     * Get application expiration.
     */
    public function getApplicationExpiration(): string
    {
        return $this->applicationExpiration ?: '1day';
    }

    /**
     * Set the application name.
     */
    public function setApplicationName(string $applicationName): Trello
    {
        $this->applicationName = $applicationName;

        return $this;
    }

    /**
     * Get application name.
     */
    public function getApplicationName():? String
    {
        return $this->applicationName ?: null;
    }

    /**
     * Set the application scope.
     */
    public function setApplicationScope(string $applicationScope): Trello
    {
        $this->applicationScope = $applicationScope;

        return $this;
    }

    /**
     * Get application scope.
     */
    public function getApplicationScope(): string
    {
        return $this->applicationScope ?: 'read';
    }

    public function urlTemporaryCredentials(): string
    {
        return 'https://trello.com/1/OAuthGetRequestToken';
    }

    public function urlAuthorization(): string
    {
        return 'https://trello.com/1/OAuthAuthorizeToken?' .
            $this->buildAuthorizationQueryParameters();
    }

    public function urlTokenCredentials(): string
    {
        return 'https://trello.com/1/OAuthGetAccessToken';
    }

    public function urlUserDetails(): string
    {
        return 'https://trello.com/1/members/me?key=' . $this->applicationKey . '&token=' . $this->accessToken;
    }

    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        $user = new User();

        $user->nickname = $data['username'];
        $user->name = $data['fullName'];
        $user->imageUrl = null;

        $user->extra = (array) $data;

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['id'];
    }

    public function userEmail($data, TokenCredentials $tokenCredentials):? string
    {
        return null;
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials):? string
    {
        return $data['username'];
    }

    /**
     * Build authorization query parameters.
     */
    private function buildAuthorizationQueryParameters(): string
    {
        $params = [
            'response_type' => 'fragment',
            'scope' => $this->getApplicationScope(),
            'expiration' => $this->getApplicationExpiration(),
            'name' => $this->getApplicationName(),
        ];

        return http_build_query($params);
    }

    /**
     * Parse configuration array to set attributes.
     *
     * @param array $configuration
     */
    private function parseConfiguration(array $configuration = []): void
    {
        $configToPropertyMap = [
            'identifier' => 'applicationKey',
            'expiration' => 'applicationExpiration',
            'name' => 'applicationName',
            'scope' => 'applicationScope',
        ];

        foreach ($configToPropertyMap as $config => $property) {
            if (isset($configuration[$config])) {
                $this->$property = $configuration[$config];
            }
        }
    }
}
