<?php

namespace League\OAuth1\Client\Credentials;

interface ClientCredentialsInterface extends CredentialsInterface
{
    /**
     * Get the credentials callback URI.
     */
    public function getCallbackUri(): string;

    /**
     * Set the credentials callback URI.
     */
    public function setCallbackUri(string $callbackUri): void;
}
