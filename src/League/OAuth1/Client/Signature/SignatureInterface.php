<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;

interface SignatureInterface
{
    /**
     * Create a new signature instance.
     *
     * @param  ClientCredentialsInterface  $clientCredentials
     */
    public function __construct(ClientCredentialsInterface $clientCredentials);

    /**
     * Set token credentials.
     *
     * @param  CredentialsInterface  $tokenCredentials
     * @return void
     */
    public function setTokenCredentials(CredentialsInterface $tokenCredentials);

    /**
     * Get the OAuth signature method.
     *
     * @return string
     */
    public function method();

    /**
     * Sign the given request for the client.
     *
     * @param  string  $uri
     * @param  array   $credentials
     * @param  string  $method
     * @return string
     */
    public function sign($uri, array $parameters = array(), $method = 'POST');
}