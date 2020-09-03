<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;

abstract class Signature implements SignatureInterface
{
    /** @var ClientCredentialsInterface */
    protected $clientCredentials;

    /** @var CredentialsInterface */
    protected $credentials;

    public function __construct(ClientCredentialsInterface $clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;
    }

    public function setCredentials(CredentialsInterface $credentials): void
    {
        $this->credentials = $credentials;
    }

    /**
     * Generate a signing key.
     */
    protected function key(): string
    {
        $key = rawurlencode($this->clientCredentials->getSecret()) . '&';

        if (null !== $this->credentials) {
            $key .= rawurlencode($this->credentials->getSecret());
        }

        return $key;
    }
}
