<?php

namespace League\OAuth1\Client\Credentials;

abstract class Credentials implements CredentialsInterface
{
    /**
     * The credentials identifier.
     *
     * @var null|string
     */
    protected $identifier;

    /**
     * The credentials secret.
     *
     * @var null|string
     */
    protected $secret;

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * {@inheritDoc}
     */
    public function setSecret($secret): void
    {
        $this->secret = $secret;
    }
}
