<?php

namespace League\OAuth1\Client\Credentials;

use GuzzleHttp\Psr7\Uri;

class ClientCredentials extends Credentials
{
    /** @var Uri|string */
    private $callbackUri;

    /** @var string|null */
    private $realm;

    /**
     * @param Uri|string $callbackUri
     */
    public function __construct(string $identifier, string $secret, $callbackUri, string $realm = null)
    {
        parent::__construct($identifier, $secret);

        $this->callbackUri = $callbackUri;
        $this->realm       = $realm;
    }

    public function getCallbackUri(): Uri
    {
        return new Uri($this->callbackUri);
    }

    public function getRealm(): ?string
    {
        return $this->realm;
    }
}
