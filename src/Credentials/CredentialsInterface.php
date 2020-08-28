<?php

namespace League\OAuth1\Client\Credentials;

interface CredentialsInterface
{
    /**
     * Get the credentials identifier.
     */
    public function getIdentifier(): ?string;

    /**
     * Set the credentials identifier.
     */
    public function setIdentifier(string $identifier): void;

    /**
     * Get the credentials secret.
     */
    public function getSecret(): ?string;

    /**
     * Set the credentials secret.
     */
    public function setSecret(string $secret): void;
}
