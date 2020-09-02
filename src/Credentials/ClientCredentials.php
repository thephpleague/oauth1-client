<?php

namespace League\OAuth1\Client\Credentials;

use GuzzleHttp\Psr7\Uri;

class ClientCredentials extends Credentials
{
    /** @var Uri|string */
    private $callbackUri;

    /**
     * @param Uri|string $callbackUri
     */
    public function __construct(string $identifier, string $secret, $callbackUri)
    {
        parent::__construct($identifier, $secret);

        $this->callbackUri = $callbackUri;
    }

    public function getCallbackUri(): Uri
    {
        return new Uri($this->callbackUri);
    }
}