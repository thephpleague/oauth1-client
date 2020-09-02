<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;

interface SignatureInterface
{
    /**
     * Create a new signature instance.
     */
    public function __construct(ClientCredentialsInterface $clientCredentials);

    /**
     * Set the credentials used in the signature. These can be temporary
     * credentials when getting token credentials during the OAuth
     * authentication process, or token credentials when querying
     * the API.
     */
    public function setCredentials(CredentialsInterface $credentials): void;

    /**
     * Get the OAuth signature method.
     */
    public function method(): string;

    /**
     * Sign the given request for the client.
     */
    public function sign(string $uri, array $parameters = [], string $method = 'POST'): string;
}
