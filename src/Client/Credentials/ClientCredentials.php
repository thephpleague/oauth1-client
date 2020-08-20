<?php

namespace League\OAuth1\Client\Credentials;

class ClientCredentials extends Credentials implements ClientCredentialsInterface
{
    /**
     * The credentials callback URI.
     *
     * @var string
     */
    protected $callbackUri;

    /**
     * {@inheritDoc}
     */
    public function getCallbackUri(): string
    {
        return $this->callbackUri;
    }

    /**
     * {@inheritDoc}
     */
    public function setCallbackUri(string $callbackUri): void
    {
        $this->callbackUri = $callbackUri;
    }
}
