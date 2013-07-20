<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;

abstract class Signature implements SignatureInterface
{
    protected $clientCredentials;

    protected $tokenCredentials;

    /**
     * Create a new signature instance.
     *
     * @param  ClientCredentialsInterface  $clientCredentials
     */
    public function __construct(ClientCredentialsInterface $clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;
    }

    /**
     * Set token credentials.
     *
     * @param  CredentialsInterface  $tokenCredentials
     * @return void
     */
    public function setTokenCredentials(CredentialsInterface $tokenCredentials)
    {
        $this->tokenCredentials = $tokenCredentials;
    }

    /**
     * Generate a signing key.
     *
     * @return string
     */
    protected function key()
    {
        $key = rawurlencode($this->clientCredentials->getSecret()).'&';

        if ($this->tokenCredentials !== null) {
            $key .= rawurlencode($this->tokenCredentials->getSecret());
        }

        return $key;
    }
}