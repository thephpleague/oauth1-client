<?php

namespace League\OAuth1\Client;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Provider\Provider;

class ClientConfig
{
    /** @var string */
    private $provider;

    /** @var ClientCredentials */
    private $credentials;

    private $httpClientOptions = [];

    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param Provider|string $provider
     */
    public function setProvider($provider): ClientConfig
    {
        $this->provider = $provider;

        return $this;
    }

    public function getCredentials(): ClientCredentials
    {
        return $this->credentials;
    }

    public function setCredentials(ClientCredentials $credentials): ClientConfig
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function getHttpClientOptions(): array
    {
        return $this->httpClientOptions;
    }

    public function setHttpClientOptions(array $httpClientOptions): ClientConfig
    {
        $this->httpClientOptions = $httpClientOptions;

        return $this;
    }
}